<?php

declare(strict_types=1);

namespace App\Domain\Article;

use App\Domain\Category\CategoryListView;
use App\Domain\PaginatedViewInterface;

class ArticleListView implements PaginatedViewInterface
{
    private ArticleInterface|null $promoted;
    /**
     * @var ArticleInterface[]
     */
    private array $side;
    private CategoryListView $categories;

    /**
     * @var array<int|string, mixed>
     */
    private array $articles;

    public function __construct()
    {
        $this->promoted = null;
        $this->side = [];
        $this->categories = new CategoryListView();
    }

    public function setPromoted(ArticleInterface $result): void
    {
        $this->promoted = $result;
    }

    public function getPromoted(): ArticleInterface|null
    {
        return $this->promoted;
    }

    public function getCategories(): CategoryListView
    {
        return $this->categories;
    }

    public function addCategory(string $tag): void
    {
        $this->categories->add($tag);
    }

    public function addSide(ArticleInterface $article): void
    {
        $this->side[] = $article;
    }

    /**
     * @return array<string|int, ArticleInterface>
     */
    public function getSide(): array
    {
        return $this->side;
    }

    /**
     * @param int<1, max> $limit
     *
     * @return $this
     */
    public function page(int $page, int $limit = 6): self
    {
        $articles = $this->getArticles();
        $ac = array_chunk($articles, $limit);
        $this->articles = $ac[$page - 1] ?? [];

        return $this;
    }

    /**
     * @param int<1, max> $limit
     */
    public function pages(int $limit = 6): int
    {
        $fm = $this->getArticles();

        return count(array_chunk($fm, $limit)) + 1;
    }

    public function addArticle(ArticleInterface $item): void
    {
        if ('main' === $item->getFrontMatter()['promoted']) {
            $this->setPromoted($item);
        }
        if ('side' === $item->getFrontMatter()['promoted']) {
            $this->addSide($item);
        }
        /** @var array<int|string, mixed> $tags */
        $tags = $item->getFrontMatter()['tags'];
        /** @var string $tag */
        foreach ($tags as $tag) {
            $this->addCategory($tag);
        }

        $this->articles[] = $item;
    }

    /**
     * @return array<int|string, mixed>
     */
    public function getArticles(): array
    {
        return $this->articles;
    }
}
