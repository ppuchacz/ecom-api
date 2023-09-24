<?php

namespace App\Controller;

use App\Entity\Category;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/category', name: 'app_category_')]
class CategoryController extends AbstractController
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
        $categories = $this->entityManager->getRepository(Category::class)->findBy([], null, $limit, $offset);
        $json = $this->serializer->serialize($categories, JsonEncoder::FORMAT);

        return JsonResponse::fromJsonString($json);
    }

    #[Route('/', name: 'add', methods: [Request::METHOD_POST])]
    public function add(Request $request): Response
    {
        $json = $request->getContent();
        $category = $this->serializer->deserialize($json, Category::class, 'json');
        $this->entityManager->persist($category);

        $this->entityManager->flush();
        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/{id}', name: 'edit', methods: [Request::METHOD_PUT])]
    public function edit(Request $request, int $id): Response
    {
        $categoryInDB = $this->entityManager->getRepository(Category::class)->findOneBy(['id' => $id]);
        if ($categoryInDB === null) {
            return new Response("Category with id $id not found", Response::HTTP_NOT_FOUND);
        }

        $json = $request->getContent();
        $category = $this->serializer->deserialize($json, Category::class, 'json');

        $categoryInDB->name = $category->name;

        $this->entityManager->persist($category);
        $this->entityManager->flush();
        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/{id}', name: 'delete', methods: [Request::METHOD_DELETE])]
    public function delete(Request $request, int $id): Response
    {
        $category = $this->entityManager->getRepository(Category::class)->findOneBy(['id' => $id]);
        if ($category === null) {
            return new Response("Category with id $id not found", Response::HTTP_NOT_FOUND);
        }

        $this->entityManager->remove($category);
        $this->entityManager->flush();
        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}