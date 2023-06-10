<?php

namespace App\Controller;

use App\Entity\Article;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\RedirectResponse;

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
        if ($request->isMethod('POST')) {
            // Récupérer les données du formulaire
            $nom = $request->request->get('nom');
            $price = $request->request->get('price');
            $quantite = $request->request->get('quantite');
            $description = $request->request->get('description');

            // Créer une nouvelle instance d'Article
            $article = new Article();
            $article->setNom($nom);
            $article->setPrice($price);
            $article->setQuantite($quantite);
            $article->setDescription($description);

            // Enregistrer l'article en base de données
            $this->entityManager->persist($article);
            $this->entityManager->flush();

            // Rediriger vers la liste des articles
            return $this->redirectToRoute('app_article');
        }

        return $this->render('article/new.html.twig');
    }

    #[Route('/article/edit/{id}', name: 'app_article_edit', methods: ['GET', 'POST'], requirements: ['id' => '\d+'])]
    public function edit(Request $request, Article $article, $id): Response
    {
        $entityManager = $this->entityManager;
        $article = $entityManager->getRepository(Article::class)->find($id);

        // Création du formulaire de modification
        $form = $this->createFormBuilder($article)
            ->add('nom', TextType::class)
            ->add('price', NumberType::class)
            ->add('quantite', NumberType::class)
            ->add('description', TextareaType::class)
            ->add('save', SubmitType::class, ['label' => 'Modifier'])
            //->add('_token', HiddenType::class)
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Enregistrer les modifications en base de données
            $this->entityManager->flush();

            // Rediriger vers la liste des articles
            return $this->redirectToRoute('app_article');
        } else {
            print($form->getErrors(true, false));
        }

        return $this->render('article/edit.html.twig', [
            'form' => $form->createView(),
            'article' => $article,
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
        } else {
            print($form->getErrors(true, false));
        }

        return $this->render('article/delete.html.twig', [
            'article' => $article,
            'deleteForm' => $form->createView(),
        ]);
    }

}