<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class LigairController extends AbstractController
{
    #[Route('/', name: 'app_ligair')]
    public function index(): Response
    {
        return $this->redirectToRoute('admin');
    }
}