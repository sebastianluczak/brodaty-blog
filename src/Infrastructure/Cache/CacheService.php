<?php
declare(strict_types=1);

namespace App\Infrastructure\Cache;

use App\Domain\Article\ArticleInterface;
use App\Domain\Article\CachedArticle;
use App\Domain\Article\CachedArticleInterface;
use App\Domain\Article\CachedArticleNotFoundException;
use App\Infrastructure\CommonMarkService;
use League\CommonMark\MarkdownConverter;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use SplFileInfo;
use Symfony\Component\Cache\CacheItem;
use Symfony\Component\Finder\Finder;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

class CacheService implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    const ARTICLES_DIRECTORY = '/resources/articles';
    const ARTICLES_CACHED_DIRECTORY = '/resources/cached';

    private MarkdownConverter $markdownConverter;
    private Finder $cachedArticlesFinder;
    private Finder $freshArticlesFinder;

    public function __construct(readonly protected CacheInterface $cache, readonly protected CommonMarkService $commonMarkService, readonly private string $projectDir)
    {
        $this->markdownConverter = $this->commonMarkService->init();
        $this->cachedArticlesFinder = (new Finder())->files()->in($projectDir. self::ARTICLES_CACHED_DIRECTORY);
        $this->freshArticlesFinder = (new Finder())->files()->in($projectDir . self::ARTICLES_DIRECTORY);
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
        $contentFilePath = $this->projectDir . self::ARTICLES_CACHED_DIRECTORY . '/' . $fileName . '_content.cached';
        $fmFilePath = $this->projectDir . self::ARTICLES_CACHED_DIRECTORY . '/' . $fileName . '_fm.cached';

        /** @var SplFileInfo $file */
        foreach ($this->cachedArticlesFinder->files() as $file) {
            if ($file->getPathname() === $contentFilePath) {
                $this->logger->info("Item " . $filePath . " retrieved from cache as " . $contentFilePath);
                return new CachedArticle(file_get_contents($contentFilePath), json_decode(file_get_contents($fmFilePath), true));
            }
        }

        throw new CachedArticleNotFoundException();
    }

    public function storeItem(string $filePath): ArticleInterface
    {
        $fileName = md5($filePath);
        $contentFilePath = $this->projectDir . self::ARTICLES_CACHED_DIRECTORY . '/' . $fileName . '_content.cached';
        $fmFilePath = $this->projectDir . self::ARTICLES_CACHED_DIRECTORY . '/' . $fileName . '_fm.cached';

        $this->logger->info("Item " . $filePath . " storing procedure start.");

        if ($this->hasItem($contentFilePath)) {
            $this->removeItem($filePath);
            $this->logger->info("Item " . $filePath . " cache invalidated and stored once again.");
        }
        $html = $this->markdownConverter->convert(file_get_contents($filePath));
        $content = $html->getContent();
        $frontMatters = $html->getFrontMatter();
        file_put_contents($contentFilePath, $content);
        file_put_contents($fmFilePath, json_encode($frontMatters));

        $this->logger->info("Item " . $filePath . " stored as " . $contentFilePath . ' in cache');

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
}