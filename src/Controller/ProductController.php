<?php

namespace App\Controller;

use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
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

    #[Route('/', name: 'list', methods: [Request::METHOD_GET])]
    public function get(#[MapQueryParameter] int $page = 1, #[MapQueryParameter] int $limit = 10): Response
    {
        if ($page < 1) {
            return new JsonResponse("Invalid PAGE query parameter - should be greater than 0");
        }

        if ($limit < 1) {
            return new JsonResponse("Invalid LIMIT query parameter - should be greater than 0");
        }

        $offset = ($page - 1) * $limit;
        $products = $this->entityManager->getRepository(Product::class)->findBy([], null, $limit, $offset);
        $json = $this->serializer->serialize($products, JsonEncoder::FORMAT);

        return new Response($json);
    }

    #[Route('/', name: 'add', methods: [Request::METHOD_POST])]
    public function add(Request $request): Response
    {
        $json = $request->getContent();
        $products = $this->serializer->deserialize($json, Product::class . '[]', 'json');

        foreach ($products as $product) {
            $this->entityManager->persist($product);
        }

        $this->entityManager->flush();
        return new JsonResponse();
    }

    #[Route('/{id}', name: 'edit', methods: [Request::METHOD_PUT])]
    public function edit(Request $request, int $id): Response
    {
        $productInDB = $this->entityManager->getRepository(Product::class)->findOneBy(['id' => $id]);
        if ($productInDB === null) {
            return new Response("Product with id $id not found", Response::HTTP_NOT_FOUND);
        }

        $json = $request->getContent();
        $product = $this->serializer->deserialize($json, Product::class, 'json');

        $productInDB->name = $product->name;
        $productInDB->description = $product->description;
        $productInDB->price = $product->price;
        $productInDB->brand = $product->brand;
        $productInDB->max_quantity = $product->max_quantity;
        $productInDB->picture = $product->picture;

        $this->entityManager->persist($product);
        $this->entityManager->flush();
        return new JsonResponse();
    }
}