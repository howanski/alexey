<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/network/usage')]
class NetworkUsageController extends AbstractController
{
    #[Route('/', name: 'network_usage')]
    public function index(): Response
    {
        return $this->render('network_usage/index.html.twig', [
            'controller_name' => 'NetworkUsageController',
        ]);
    }
}
