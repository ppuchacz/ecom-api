<?php

namespace App\Controller;

use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/product', name: 'app_product_')]
class ProductController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private SerializerInterface $serializer,
    ) {}

    #[Route('/', name: 'list')]
    public function get(): Response
    {
//        $page = $this->request->get('page');
//        $limit = $this->request->get('limit');

        $products = $this->entityManager->getRepository(Product::class)->findAll();
        $json = $this->serializer->serialize($products, JsonEncoder::FORMAT);

        return new Response($json);
    }
}