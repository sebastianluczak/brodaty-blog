<?php
declare(strict_types=1);
namespace App\Domain\Article;

use App\Domain\Category\CategoryListView;
use League\CommonMark\Extension\FrontMatter\Output\RenderedContentWithFrontMatter;

class ArticleListView
{
    private RenderedContentWithFrontMatter $promoted;
    private array $frontMatters = [];
    /**
     * @var RenderedContentWithFrontMatter[]
     */
    private array $side;
    private CategoryListView $categories;

    public function __construct()
    {
        $this->categories = new CategoryListView();
    }

    public function setPromoted(RenderedContentWithFrontMatter $result): void
    {
        $this->promoted = $result;
    }

    /**
     * @return RenderedContentWithFrontMatter
     */
    public function getPromoted(): RenderedContentWithFrontMatter
    {
        return $this->promoted;
    }

    public function addFrontMatter(array $frontMatter): void
    {
        $this->frontMatters[] = $frontMatter;
    }

    /**
     * @return array
     */
    public function getFrontMatters(): array
    {
        return $this->frontMatters;
    }

    public function getCategories(): CategoryListView
    {
        return $this->categories;
    }

    public function addCategory(string $tag): void
    {
        $this->categories->add($tag);
    }

    public function addSide(RenderedContentWithFrontMatter $result): void
    {
        $this->side[] = $result;
    }

    public function getSide(): array
    {
        return $this->side;
    }

    public function page(int $page, int $limit = 6): self
    {
        $fm = $this->getFrontMatters();
        $ac = array_chunk($fm, $limit);
        $this->frontMatters = $ac[$page-1]??[];

        return $this;
    }

    public function pages(int $limit = 6): int
    {
        $fm = $this->getFrontMatters();

        return count(array_chunk($fm, $limit)) + 1;
    }
}