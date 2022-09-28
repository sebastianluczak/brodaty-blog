<?php
declare(strict_types=1);
namespace App\Application;

use App\Domain\Article\ArticleInterface;
use App\Domain\Article\ArticleListView;
use App\Domain\Article\ArticleNotFoundException;
use App\Domain\Article\CachedArticleNotFoundException;
use App\Infrastructure\Cache\CacheService;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

class ArticlesService implements LoggerAwareInterface
{
    use LoggerAwareTrait;
    const ARTICLES_DIRECTORY = '/resources/articles/';
    private string $articlesDirectory;

    public function __construct(readonly protected CacheService $cacheService, string $projectDir)
    {
        $this->articlesDirectory = $projectDir . self::ARTICLES_DIRECTORY;
    }

    /**
     * @throws ArticleNotFoundException
     */
    public function getBySlug(string $slug): ArticleInterface
    {
        // VO for article
        $articles = $this->getArticlesFinder();

        // check if there are any search results
        if ($articles->hasResults()) {
            foreach ($articles as $article) {
                // cache here
                try {
                    $result = $this->cacheService->getItem($article->getRealPath());
                } catch (CachedArticleNotFoundException $e) {
                    $result = $this->cacheService->storeItem($article->getRealPath());
                }
                $frontMatter = $result->getFrontMatter();
                if ($frontMatter['slug'] === $slug) {
                    return $result;
                }
            }
        }

        throw new ArticleNotFoundException;
    }

    public function getArticlesFinder(): Finder
    {
        $finder = new Finder();
        // find all files in the current directory
        return $finder->files()->in($this->articlesDirectory);
    }

    public function getAll(int $page = 1): ArticleListView
    {
        $articleListView = new ArticleListView();
        /** @var SplFileInfo $file */
        foreach ($this->getArticlesFinder()->files() as $file) {
            try {
                $article = $this->cacheService->getItem($file->getPathname());
            } catch (CachedArticleNotFoundException $e) {
                $article = $this->cacheService->storeItem($file->getPathname());
            }
            $articleListView->addArticle($article);
        }

        return $articleListView->page($page);
    }

    public function getAllWithTag(string $tagName, int $page = 1): ArticleListView
    {
        $articleListView = new ArticleListView();
        /** @var SplFileInfo $file */
        foreach ($this->getArticlesFinder()->files() as $file) {
            try {
                $article = $this->cacheService->getItem($file->getPathname());
            } catch (CachedArticleNotFoundException $e) {
                $article = $this->cacheService->storeItem($file->getPathname());
            }
            if ($article->hasTag($tagName)) {
                $articleListView->addArticle($article);
            }
        }

        return $articleListView->page($page);
    }
}