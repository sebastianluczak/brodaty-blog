<?php
declare(strict_types=1);
namespace App\Domain\Article;

class Article implements ArticleInterface
{
    protected array $frontMatter;
    protected string $htmlContent;

    public function getFrontMatter(): array
    {
        return $this->frontMatter;
    }

    /**
     * @return string
     */
    public function getHtmlContent(): string
    {
        return $this->htmlContent;
    }

    public function hasTag(string $tag): bool
    {
        return in_array($tag, $this->getFrontMatter()['tags']);
    }
}