<?php

declare(strict_types=1);

namespace App\Domain\Category;

class CategoryListView
{
    private array $tags;
    private array $counts;

    public function __construct()
    {
        $this->tags = [];
        $this->counts = [];
    }

    public function add(string $tag): void
    {
        if (!in_array($tag, $this->tags)) {
            $this->tags[] = $tag;
            $this->counts[$tag] = 1;
        } else {
            ++$this->counts[$tag];
        }
    }

    public function getTags(): array
    {
        return $this->tags;
    }

    public function getCounts(): array
    {
        return $this->counts;
    }
}
