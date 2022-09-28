<?php

namespace App\Application\Controller;

use App\Application\ArticlesService;
use App\Domain\Article\ArticleNotFoundException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ArticlesController extends AbstractController
{
    #[Route('/', name: 'app_articles_list')]
    public function index(ArticlesService $articlesService, Request $request): Response
    {
        $page = $request->get('page') ?? 1;
        $articleListView = $articlesService->getAll($page);

        return $this->render('blog/index.html.twig', [
            'articles' => $articleListView->getArticles(),
            'featured' => $articleListView->getPromoted(),
            'categories' => $articleListView->getCategories(),
            'side' => $articleListView->getSide(),
            'pages' => $articleListView->pages(),
            'currentPage' => $page,
        ]);
    }

    #[Route('/tag/{tag}', name: 'app_articles_by_tag')]
    public function indexByTag(string $tag, ArticlesService $articlesService, Request $request): Response
    {
        $page = $request->get('page') ?? 1;
        $articleListView = $articlesService->getAllWithTag($tag, $page);

        return $this->render('blog/index.html.twig', [
            'articles' => $articleListView->getArticles(),
            'featured' => $articleListView->getPromoted(),
            'categories' => $articleListView->getCategories(),
            'side' => $articleListView->getSide(),
            'pages' => $articleListView->pages(),
            'currentPage' => $page,
        ]);
    }

    #[Route('/{name}', name: 'app_articles_single')]
    public function single(string $name, ArticlesService $articlesService): Response
    {
        try {
            $article = $articlesService->getBySlug($name);
        } catch (ArticleNotFoundException $e) {
            return $this->render('404.html.twig');
        }

        return $this->render('blog/single.html.twig', [
            'article' => $article->getHtmlContent(),
            'frontMatter' => $article->getFrontMatter(),
        ]);
    }
}
