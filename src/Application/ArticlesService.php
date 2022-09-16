<?php
declare(strict_types=1);
namespace App\Application;

use App\Domain\Article\ArticleListView;
use App\Infrastructure\CommonMarkService;
use League\CommonMark\Extension\FrontMatter\Output\RenderedContentWithFrontMatter;
use League\CommonMark\MarkdownConverter;
use League\CommonMark\Output\RenderedContentInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Finder\Finder;

class ArticlesService
{
    const ARTICLES_DIRECTORY = '../resources/articles/';

    private MarkdownConverter $markdownConverter;
    private FilesystemAdapter $cache;

    public function __construct(readonly protected CommonMarkService $converter)
    {
        $this->markdownConverter = $converter->init();
        $this->cache = new FilesystemAdapter();
    }

    public function getBySlug(string $slug): RenderedContentWithFrontMatter
    {
        $finder = new Finder();
        // find all files in the current directory
        $articles = $finder->files()->in(self::ARTICLES_DIRECTORY);
        // VO for article
        $articlesConverted = [];
        // check if there are any search results
        if ($articles->hasResults()) {
            foreach ($articles as $article) {
                // cache here
                $result = $this->getHtmlFromCacheForFile($article->getRealPath());
                if (!$result) {
                    $result = $this->markdownConverter->convert(file_get_contents($article->getRealPath()));
                }
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
    }

    public function articlesList(int $page = 1): ArticleListView
    {
        $articleListView = new ArticleListView();

        $finder = new Finder();
        // find all files in the current directory
        $articles = $finder->files()->in(self::ARTICLES_DIRECTORY);
        // VO for article
        // check if there are any search results
        if ($articles->hasResults()) {
            foreach ($articles as $article) {
                // cache here
                $result = $this->getHtmlFromCacheForFile($article->getRealPath());
                if (!$result) {
                    $result = $this->markdownConverter->convert(file_get_contents($article->getRealPath()));
                }
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

        $finder = new Finder();
        // find all files in the current directory
        $articles = $finder->files()->in(self::ARTICLES_DIRECTORY);
        // VO for article
        // check if there are any search results
        if ($articles->hasResults()) {
            foreach ($articles as $article) {
                // cache here
                $result = $this->getHtmlFromCacheForFile($article->getRealPath());
                if (!$result) {
                    $result = $this->markdownConverter->convert(file_get_contents($article->getRealPath()));
                }
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

    public function clearCache(): void
    {
        $this->cache->clear();
    }

    private function getHtmlFromCacheForFile(string $filePath): RenderedContentInterface|null
    {
        $htmlContentConverted = $this->cache->getItem('article_' . $filePath);

        if (!$htmlContentConverted->isHit()) {
            $htmlContentConverted->set($this->markdownConverter->convert(file_get_contents($filePath)));
            $htmlContentConverted->expiresAfter(60 * 60 * 24);
            $this->cache->save($htmlContentConverted);
        }

        return $htmlContentConverted->get();
    }

    public function initCache(): void
    {
        // todo better way
        $this->articlesList();
    }
}