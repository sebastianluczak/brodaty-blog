<?php
declare(strict_types=1);
namespace App\Domain;

interface PaginatedViewInterface
{
    public function page(int $page, int $limit = 6): self;

    public function pages(int $limit = 6): int;
}