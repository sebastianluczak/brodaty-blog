<?php
declare(strict_types=1);
namespace App\Application;

use App\Domain\Article\ArticleListView;
use App\Domain\Article\ArticleNotFoundException;
use App\Infrastructure\Cache\CacheService;
use League\CommonMark\Extension\FrontMatter\Output\RenderedContentWithFrontMatter;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\Finder\Finder;

class ArticlesService implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    const ARTICLES_DIRECTORY = '../resources/articles/';

    public function __construct(readonly protected CacheService $cacheService)
    {
    }

    /**
     * @throws ArticleNotFoundException
     */
    public function getBySlug(string $slug): RenderedContentWithFrontMatter
    {
        // VO for article
        $articles = $this->getArticlesFinder();

        // check if there are any search results
        if ($articles->hasResults()) {
            foreach ($articles as $article) {
                // cache here
                $result = $this->cacheService->getFromCache($article->getRealPath());

                // Grab the front matter:
                $frontMatter = null;
                if ($result instanceof RenderedContentWithFrontMatter) {
                    $frontMatter = $result->getFrontMatter();

                    if ($frontMatter['slug'] === $slug) {
                        return $result;
                    }
                }
            }
        }

        throw new ArticleNotFoundException;
    }

    public function articlesList(int $page = 1): ArticleListView
    {
        $articleListView = new ArticleListView();
        // VO for article
        $articles = $this->getArticlesFinder();

        // check if there are any search results
        if ($articles->hasResults()) {
            foreach ($articles as $article) {
                // cache here
                $result = $this->cacheService->getFromCache($article->getRealPath());

                // Grab the front matter:
                if ($result instanceof RenderedContentWithFrontMatter) {
                    $articleListView->addFrontMatter($result->getFrontMatter());
                    if ($result->getFrontMatter()['promoted'] === "main") {
                        $articleListView->setPromoted($result);
                    }
                    if ($result->getFrontMatter()['promoted'] === "side") {
                        $articleListView->addSide($result);
                    }
                    foreach ($result->getFrontMatter()['tags'] as $tag) {
                        $articleListView->addCategory($tag);
                    }
                }
            }
        }

        return $articleListView->page($page);
    }

    public function articlesListWithTag(string $tagName, int $page = 1): ArticleListView
    {
        $articleListView = new ArticleListView();
        // VO for article
        $articles = $this->getArticlesFinder();

        // check if there are any search results
        if ($articles->hasResults()) {
            foreach ($articles as $article) {
                // cache here
                $result = $this->cacheService->getFromCache($article->getRealPath());

                // Grab the front matter:
                if ($result instanceof RenderedContentWithFrontMatter) {
                    if (in_array($tagName, $result->getFrontMatter()['tags'])) {
                        $articleListView->addFrontMatter($result->getFrontMatter());
                    }
                    if ($result->getFrontMatter()['promoted'] === "main") {
                        $articleListView->setPromoted($result);
                    }
                    if ($result->getFrontMatter()['promoted'] === "side") {
                        $articleListView->addSide($result);
                    }
                    foreach ($result->getFrontMatter()['tags'] as $tag) {
                        $articleListView->addCategory($tag);
                    }
                }
            }
        }

        return $articleListView->page($page);
    }

    public function getArticlesFinder(): Finder
    {
        $finder = new Finder();
        // find all files in the current directory
        return $finder->files()->in(self::ARTICLES_DIRECTORY);
    }
}