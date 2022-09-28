<?php

declare(strict_types=1);

namespace App\Domain\Article;

final class CachedArticle extends Article implements CachedArticleInterface
{
    public function __construct(protected string $htmlContent, protected array $frontMatter)
    {
    }
}
