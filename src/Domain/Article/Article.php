<?php

declare(strict_types=1);

namespace App\Domain\Article;

class Article implements ArticleInterface
{
    /**
     * @var array<string, mixed>
     */
    protected array $frontMatter;

    protected string $htmlContent;

    /**
     * @return array<string, mixed>
     */
    public function getFrontMatter(): array
    {
        return $this->frontMatter;
    }

    public function getHtmlContent(): string
    {
        return $this->htmlContent;
    }

    public function hasTag(string $tag): bool
    {
        $tags = $this->getFrontMatter()['tags'];
        if (is_array($tags)) {
            return in_array($tag, $tags);
        }

        return false;
    }
}
