<?php

namespace App\Controller;

use App\Entity\Category;
use App\Form\CategoryType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;


#[Route('{_locale}')]
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

            $this->addFlash('success', 'Catégorie ajoutée avec succès');

            return $this->redirectToRoute('app_categorie');
        }
        
        $categories = $em->getRepository(Category::class)->findAll();

        return $this->render('categorie/index.html.twig', [
            'categories' => $categories,
            'addCategory' => $form
        ]);
    }

    #[Route('/category/show/{id}', name: 'app_category_show')]
    public function show(Category $category = null, Request $request, EntityManagerInterface $em): Response
    {
        if($category === null){
            $this->addFlash('warning', 'Catégorie introuvable');
            return $this->redirectToRoute('app_categorie');
        }

        $form = $this->createForm(CategoryType::class, $category);

        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $em->persist($category);
            $em->flush();

            $this->addFlash('success', 'Catégorie modifiée avec succès');

            return $this->redirectToRoute('app_categorie');
        }

        return $this->render('categorie/show.html.twig', [
            'category' => $category,
            'editCategory' => $form
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

        $this->addFlash('success', 'Catégorie ajoutée avec succès');

        return $this->redirectToRoute('app_categorie');
    }

    #[Route('/category/edit/{id}', name: 'app_category_edit')]
    public function edit(Category $category = null, EntityManagerInterface $em): Response
    {
        if($category === null){
            $this->addFlash('warning', 'Catégorie introuvable');
            return $this->redirectToRoute('app_categorie');
        }

        $category->setDescription(description: 'Description de la catégorie');

        $em->persist($category);
        $em->flush();

        $this->addFlash('success', 'Catégorie modifiée avec succès');

        return $this->redirectToRoute('app_categorie');
    }

    #[Route('/category/delete/{id}', name: 'app_category_delete')]
    public function delete(Request $request, EntityManagerInterface $em, Category $category)
    {
        if ($this->isCsrfTokenValid('delete' . $category->getId(), $request->request->get('csrf'))) {
            $em->remove($category);
            $em->flush();

            $this->addFlash('success', 'Catégorie supprimée avec succès');
        }
        return $this->redirectToRoute('app_categorie');
    }
}
