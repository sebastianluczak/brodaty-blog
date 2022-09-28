<?php

namespace App\Tests;

use App\Application\ArticlesService;
use App\Domain\Article\Article;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class UnitTest extends KernelTestCase
{
    public function testDomainArticle()
    {
        $stub = $this->createStub(Article::class);
        $stub->method('getFrontMatter')
            ->willReturn(['title' => 'someTitle']);

        $this->assertSame('someTitle', $stub->getFrontMatter()['title']);

        $stub->method('getHtmlContent')
            ->willReturn("some html string");

        $this->assertSame('some html string', $stub->getHtmlContent());
    }
}