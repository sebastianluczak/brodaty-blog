<?php

declare(strict_types=1);

namespace App\Application\Exception;

use App\Domain\Article\ArticleNotFoundExceptionInterface;

class ArticleNotFoundException extends \Exception implements ArticleNotFoundExceptionInterface
{
}
