<?php

namespace App\Controller;

use App\Entity\Article;
use App\Form\ArticleType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;

class ArticleController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/article', name: 'app_article')]
    public function index(): Response
    {
        $articleRepository = $this->entityManager->getRepository(Article::class);
        $articles = $articleRepository->findAll();

        return $this->render('article/index.html.twig', [
            'articles' => $articles,
        ]);
    }

    #[Route('/article/new', name: 'app_article_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $article = new Article();
        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->entityManager;
            $em->persist($article);
            $em->flush();

            return $this->redirectToRoute('app_article');
        }

        return $this->render('article/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/article/delete/{id}', name: 'app_article_delete', methods: ['GET', 'POST'], requirements: ['id' => '\d+'])]
    public function delete(Article $article, Request $request, $id): Response
    {
        $entityManager = $this->entityManager;
        $article = $entityManager->getRepository(Article::class)->find($id);
        $form = $this->createFormBuilder()
            ->setAction($this->generateUrl('app_article_delete', ['id' => $article->getId()]))
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->entityManager;
            $em->remove($article);
            $em->flush();

            $this->addFlash('success', 'L\'article a été supprimé avec succès.');

            return $this->redirectToRoute('app_article');
        }

        return $this->render('article/delete.html.twig', [
            'article' => $article,
            'deleteForm' => $form->createView(),
        ]);
    }

    #[Route('/article/edit/{id}', name: 'app_article_edit', methods: ['GET', 'POST'], requirements: ['id' => '\d+'])]
    public function edit(Article $article, Request $request, $id): Response{
        $entityManager = $this->entityManager;
        $article = $entityManager->getRepository(Article::class)->find($id);

        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {
            $em = $this->entityManager;
            $em->flush();

            return $this->redirectToRoute('app_article');
        }
    
        return $this->render("article/edit.html.twig", [
            "form" => $form->createView(),
            'articles' => $article
        ]);
    
    }

}
