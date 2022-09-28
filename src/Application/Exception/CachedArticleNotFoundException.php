<?php

declare(strict_types=1);

namespace App\Application\Exception;

use App\Domain\Article\ArticleNotFoundExceptionInterface;

class CachedArticleNotFoundException extends \Exception implements ArticleNotFoundExceptionInterface
{
}
