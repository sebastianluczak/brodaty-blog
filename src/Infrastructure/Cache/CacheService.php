<?php
declare(strict_types=1);

namespace App\Infrastructure\Cache;

use App\Infrastructure\CommonMarkService;
use League\CommonMark\MarkdownConverter;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\Cache\CacheItem;
use Symfony\Component\Finder\Finder;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

class CacheService implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    private MarkdownConverter $markdownConverter;

    public function __construct(readonly protected CacheInterface $cache, readonly protected CommonMarkService $commonMarkService)
    {
        $this->markdownConverter = $this->commonMarkService->init();
    }

    public function clearCache(string $class)
    {
        $this->cache->clear();
    }

    public function getFromCache(string $filePath)
    {
        $this->logger->info("Getting Article: " . $filePath);
        /** @var CacheItem $cacheItem */

        return $this->cache->get('article_' . $filePath, function (ItemInterface $item) use ($filePath) {
            $this->logger->info("Not cached item.");
            $time = microtime(true);
            $html = $this->markdownConverter->convert(file_get_contents($filePath));
            $item->expiresAt(new \DateTime('tomorrow'));
            $this->logger->info("Cache write for" . $filePath . ", took: " . microtime(true) - $time);

            return $html;
        });
    }

    public function clearAndPopulate(Finder $articlesFinder)
    {
        $this->cache->clear();

        if ($articlesFinder->hasResults()) {
            foreach ($articlesFinder as $article) {
                // cache here
                $this->getFromCache($article->getRealPath());
            }
        }
    }
}