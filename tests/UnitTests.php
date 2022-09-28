<?php

// tests/Service/NewsletterGeneratorTest.php
namespace App\Tests;

use App\Application\ArticlesService;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class UnitTests extends KernelTestCase
{
    public function testArticlesNotEmpty()
    {
        // (1) boot the Symfony kernel
        self::bootKernel();

        // (2) use static::getContainer() to access the service container
        $container = static::getContainer();

        /** @var ArticlesService $articlesService */
        $articlesService = $container->get(ArticlesService::class);
        $articles = $articlesService->getAll();

        $this->assertNotEmpty($articles->getArticles());
    }
}