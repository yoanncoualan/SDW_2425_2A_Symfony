<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('{_locale}')]
final class UserController extends AbstractController{
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/users', name: 'app_user')]
    public function index(EntityManagerInterface $em): Response
    {
        return $this->render('user/index.html.twig', [
            'users' => $em->getRepository(User::class)->findRoleAdmin()
        ]);
    }
}
