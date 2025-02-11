<?php

namespace App\Controller;

use App\Entity\Product;
use App\Form\ProductType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('{_locale}')]
final class ProductController extends AbstractController{
    #[Route('/product', name: 'app_product')]
    public function index(Request $request, EntityManagerInterface $em, SluggerInterface $slugger, TranslatorInterface $t): Response
    {
        $product = new Product();
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);
 
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $imageFile */
            $imageFile = $form->get('image')->getData();
 
            if ($imageFile) {
                $newFilename = $slugger->slug($product->getTitle()).'.'.$imageFile->guessExtension();
 
                try {
                    $imageFile->move(
                        $this->getParameter('upload_directory'),
                        $newFilename
                    );
                    $product->setImage($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('warning', 'Impossible d\'ajouter l\'image');
                }
            }
 
            $product->setCreatedDate(new \DateTime());
            $em->persist($product);
            $em->flush();

            $this->addFlash('success', $t->trans('Product.added'));
            return $this->redirectToRoute('app_product');
        }

        $products = $em->getRepository(Product::class)->findAll();
 
        return $this->render('product/index.html.twig', [
            'add_product' => $form,
            'products' => $products
        ]);
    }


    #[IsGranted('ROLE_ADMIN')]
    #[Route('/product/delete/{id}', name: 'app_product_delete')]
    public function delete(Request $request, EntityManagerInterface $em, Product $product)
    {
        if ($this->isCsrfTokenValid('delete' . $product->getId(), $request->request->get('csrf'))) {
            $em->remove($product);
            $em->flush();
            
            $this->addFlash('success', 'Produit supprimée avec succès');
        }
        return $this->redirectToRoute('app_product');
    }

    #[Route('/product/show/{id}', name: 'app_product_show')]
    public function show(Product $product = null, Request $request, EntityManagerInterface $em, SluggerInterface $slugger): Response
    {
        if($product === null){
            $this->addFlash('warning', 'Produit introuvable');
            return $this->redirectToRoute('app_product');
        }

        $form = $this->createForm(ProductType::class, $product);

        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            /** @var UploadedFile $imageFile */
            $imageFile = $form->get('image')->getData();
 
            if ($imageFile) {
                // Suppression de l'ancienne image :
                unlink($this->getParameter('upload_directory').'/'.$product->getImage());
                
                $newFilename = $slugger->slug($product->getTitle()).'.'.$imageFile->guessExtension();
 
                try {
                    $imageFile->move(
                        $this->getParameter('upload_directory'),
                        $newFilename
                    );
                    $product->setImage($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('warning', 'Impossible d\'ajouter l\'image');
                }
            }

            $em->persist($product);
            $em->flush();

            $this->addFlash('success', 'Produit modifié avec succès');

            return $this->redirectToRoute('app_product');
        }

        return $this->render('product/show.html.twig', [
            'product' => $product,
            'edit_product' => $form
        ]);
    }

    #[Route("/product/delete-image/{id}", name: "app_product_delete_image")]
    public function deleteImage(Product $product = null, EntityManagerInterface $em){
        if($product === null){
            $this->addFlash('warning', 'Produit introuvable');
            return $this->redirectToRoute('app_product');
        }

        unlink($this->getParameter('upload_directory').'/'.$product->getImage());

        $product->setImage(null);
        $em->persist($product);
        $em->flush();
        
        $this->addFlash('success', 'Image supprimée avec succès');
        return $this->redirectToRoute('app_product');
    }
}
