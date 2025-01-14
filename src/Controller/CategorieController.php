<?php

namespace App\Controller;

use App\Entity\Category;
use App\Form\CategoryType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class CategorieController extends AbstractController{

    #[Route('/', name: 'app_categorie')]
    public function index(EntityManagerInterface $em, Request $request): Response
    {
        $category = new Category();
        $form = $this->createForm(CategoryType::class, $category);

        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $em->persist($category);
            $em->flush();

            return $this->redirectToRoute('app_categorie');
        }
        
        $categories = $em->getRepository(Category::class)->findAll();

        return $this->render('categorie/index.html.twig', [
            'categories' => $categories,
            'addCategory' => $form
        ]);
    }

    #[Route('/category/show/{id}', name: 'app_category_show')]
    public function show(Category $category = null): Response
    {
        if($category === null){
            return $this->redirectToRoute('app_categorie');
        }

        return $this->render('category/show.html.twig', [
            'category' => $category,
        ]);
    }

    #[Route('/category/create', name: 'app_category_create')]
    public function create(EntityManagerInterface $em): Response
    {
        $category = new Category(); 
        $category->setTitle('Nouvelle catégorie');

        // Prépare la requête SQL
        $em->persist($category);
        // Exécute la requête SQL
        $em->flush();

        return $this->redirectToRoute('app_categorie');
    }

    #[Route('/category/edit/{id}', name: 'app_category_edit')]
    public function edit(Category $category = null, EntityManagerInterface $em): Response
    {
        if($category === null){
            return $this->redirectToRoute('app_categorie');
        }

        $category->setDescription(description: 'Description de la catégorie');

        $em->persist($category);
        $em->flush();

        return $this->redirectToRoute('app_categorie');
    }
}
