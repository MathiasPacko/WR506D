<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\SlugifyService;

class ProductController extends AbstractController
{
    #[Route('/products', name: 'product_list')]
    public function listProducts(): Response
    {
        return $this->render('product/list.html.twig', [
            'title' => 'Liste des produits',
        ]);
    }

    #[Route('/product/{id}', name: 'product_view')]
    public function viewProduct(int $id, SlugifyService $slugify): Response
    {
        $title = "T-Shirt d'Été !";
        $slug = $slugify->slugify($title);

        return $this->render('product/view.html.twig', [
            'title' => "Affichage du produit $id",
            'slug' => $slug,
        ]);
    }
}
