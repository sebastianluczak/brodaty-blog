<?php

declare(strict_types=1);

namespace App\Domain\Category;

class CategoryListView
{
    /**
     * @var array<int|string, mixed>
     */
    private array $tags;

    /**
     * @var array<string, mixed>
     */
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

    /**
     * @return array<int|string, mixed>
     */
    public function getTags(): array
    {
        return $this->tags;
    }

    /**
     * @return array<string, mixed>
     */
    public function getCounts(): array
    {
        return $this->counts;
    }
}
