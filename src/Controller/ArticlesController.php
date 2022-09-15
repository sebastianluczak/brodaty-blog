<?php

// src/Controller/LuckyController.php
namespace App\Controller;

use App\Service\ArticlesService;
use App\Service\CommonMarkService;
use League\CommonMark\Extension\FrontMatter\Output\RenderedContentWithFrontMatter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Finder\Finder;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ArticlesController extends AbstractController
{
    const ARTICLES_DIRECTORY = '../resources/articles/';

    #[Route('/', name: 'app_articles_list')]
    public function index(ArticlesService $articlesService): Response
    {
        $articleListView = $articlesService->articlesList();

        return $this->render('blog/index.html.twig', [
            'frontMatters' => $articleListView->getFrontMatters(),
            'featured' => $articleListView->getPromoted(),
            'categories' => $articleListView->getCategories(),
            'side' => $articleListView->getSide()
        ]);
    }

    #[Route('/tag/{tag}', name: 'app_articles_by_tag')]
    public function indexByTag(string $tag, ArticlesService $articlesService): Response
    {
        $articleListView = $articlesService->articlesListWithTag($tag);

        return $this->render('blog/index.html.twig', [
            'frontMatters' => $articleListView->getFrontMatters(),
            'featured' => $articleListView->getPromoted(),
            'categories' => $articleListView->getCategories(),
            'side' => $articleListView->getSide()
        ]);
    }

    #[Route('/clear_cache', name: 'app_articles_clear_cache')]
    public function clearCache(ArticlesService $articlesService): JsonResponse
    {
        $then = microtime(true);
        $articlesService->clearCache();
        $articlesService->initCache();

        return new JsonResponse(['message' => 'cache cleared', 'time' => microtime(true) - $then]);
    }

    #[Route('/{name}', name: 'app_articles_single')]
    public function single(string $name, CommonMarkService $converter, ArticlesService $articlesService): Response
    {
        $result = $articlesService->getBySlug($name);

        return $this->render('blog/single.html.twig', ['article' => $result->getContent(), 'frontMatter' => $result->getFrontMatter()]);
    }
}