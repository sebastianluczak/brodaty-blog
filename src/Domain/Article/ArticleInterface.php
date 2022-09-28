<?php
declare(strict_types=1);

namespace App\Domain\Article;

interface ArticleInterface
{
    public function getFrontMatter(): array;

    public function getHtmlContent(): string;

    public function hasTag(string $tag): bool;
}