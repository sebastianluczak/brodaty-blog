<?php

declare(strict_types=1);

namespace App\Infrastructure\Cache;

use App\Application\Exception\CachedArticleNotFoundException;
use App\Domain\Article\ArticleInterface;
use App\Domain\Article\CachedArticle;
use App\Infrastructure\CommonMarkService;
use League\CommonMark\Extension\FrontMatter\Output\RenderedContentWithFrontMatter;
use League\CommonMark\MarkdownConverter;
use LogicException;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use SplFileInfo;
use Symfony\Component\Finder\Finder;
use Symfony\Contracts\Cache\CacheInterface;

class CacheService implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    public const ARTICLES_CACHED_DIRECTORY = '/resources/cached';

    private MarkdownConverter $markdownConverter;
    private Finder $cachedArticlesFinder;

    public function __construct(readonly protected CacheInterface $cache, readonly protected CommonMarkService $commonMarkService, readonly private string $projectDir)
    {
        $this->markdownConverter = $this->commonMarkService->init();
        $this->cachedArticlesFinder = (new Finder())->files()->in($projectDir.self::ARTICLES_CACHED_DIRECTORY);
    }

    private function hasItem(string $filePath): bool
    {
        /** @var SplFileInfo $file */
        foreach ($this->cachedArticlesFinder->files() as $file) {
            if ($file->getPathname() === $filePath) {
                return true;
            }
        }

        return false;
    }

    /**
     * @throws CachedArticleNotFoundException
     */
    public function getItem(string $filePath): ArticleInterface
    {
        $fileName = md5($filePath);
        $contentFilePath = $this->projectDir.self::ARTICLES_CACHED_DIRECTORY.'/'.$fileName.'_content.cached';
        $fmFilePath = $this->projectDir.self::ARTICLES_CACHED_DIRECTORY.'/'.$fileName.'_fm.cached';

        /** @var SplFileInfo $file */
        foreach ($this->cachedArticlesFinder->files() as $file) {
            if ($file->getPathname() === $contentFilePath) {
                $this->logger?->info('Item '.$filePath.' retrieved from cache as '.$contentFilePath);
                $content = file_get_contents($contentFilePath);
                $fmContent = file_get_contents($fmFilePath);
                if ($fmContent && is_string($content)) {
                    /**
                     * @var array<string, mixed> $fm
                     */
                    $fm = json_decode($fmContent, true);

                    return new CachedArticle($content, $fm);
                }
            }
        }

        throw new CachedArticleNotFoundException();
    }

    public function storeItem(string $filePath): ArticleInterface
    {
        $fileName = md5($filePath);
        $contentFilePath = $this->projectDir.self::ARTICLES_CACHED_DIRECTORY.'/'.$fileName.'_content.cached';
        $fmFilePath = $this->projectDir.self::ARTICLES_CACHED_DIRECTORY.'/'.$fileName.'_fm.cached';

        $this->logger?->info('Item '.$filePath.' storing procedure start.');

        if ($this->hasItem($contentFilePath)) {
            $this->removeItem($filePath);
            $this->logger?->info('Item '.$filePath.' cache invalidated and stored once again.');
        }
        $fileContents = file_get_contents($filePath);
        if (is_string($fileContents)) {
            $html = $this->markdownConverter->convert($fileContents);
        } else {
            throw new LogicException('Something wrong with cache.');
        }

        if ($html instanceof RenderedContentWithFrontMatter) {
            $content = $html->getContent();
            /**
             * @var array<string, mixed> $frontMatters
             */
            $frontMatters = $html->getFrontMatter();
            file_put_contents($contentFilePath, $content);
            file_put_contents($fmFilePath, json_encode($frontMatters));
        } else {
            throw new LogicException('Bad format, check your configuration of CommonMark service.');
        }

        $this->logger?->info('Item '.$filePath.' stored as '.$contentFilePath.' in cache');

        return new CachedArticle($content, $frontMatters);
    }

    public function removeItem(string $filePath): void
    {
        /** @var SplFileInfo $file */
        foreach ($this->cachedArticlesFinder->files() as $file) {
            if ($file->getRealPath() === $filePath) {
                unlink($filePath);
            }
        }
    }

    public function clear(): void
    {
        /** @var SplFileInfo $file */
        foreach ($this->cachedArticlesFinder->files() as $file) {
            unlink($file->getRealPath());
        }
    }
}
