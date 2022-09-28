<?php

declare(strict_types=1);

namespace App\Domain\Article;

interface ArticleInterface
{
    /**
     * @return array<string, mixed>
     */
    public function getFrontMatter(): array;

    public function getHtmlContent(): string;

    public function hasTag(string $tag): bool;
}
