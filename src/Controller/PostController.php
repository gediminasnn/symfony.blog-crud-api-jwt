<?php

namespace App\Controller;

use App\Interfaces\PostServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class PostController extends AbstractController
{
    private PostServiceInterface $postService;

    public function __construct(PostServiceInterface $postService)
    {
        $this->postService = $postService;
    }

    #[Route('/api/posts', name: 'post_index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        try {
            $data = $this->postService->getAllPosts(
                $request->query->getInt('page', 1),
                $request->query->getInt('limit', 10)
            );

            return $this->json($data);
        } catch (NotFoundHttpException|BadRequestHttpException $e) {
            return $this->json(['message' => $e->getMessage()], $e->getStatusCode());
        }
    }

    #[Route('/api/posts/{id}', name: 'post_show', methods: ['GET'])]
    public function show(int $id): Response
    {
        try {
            $post = $this->postService->getPost($id);

            return $this->json([
                'id' => $post->getId(),
                'title' => $post->getTitle(),
                'content' => $post->getContent(),
                'timestamp' => $post->getTimestamp()->format('Y-m-d H:i:s'),
            ]);
        } catch (NotFoundHttpException|BadRequestHttpException $e) {
            return $this->json(['message' => $e->getMessage()], $e->getStatusCode());
        }
    }

    #[Route('/api/posts', name: 'post_create', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function create(Request $request): Response
    {
        try {
            $post = $this->postService->createPost(json_decode($request->getContent(), true));

            return $this->json([
                'id' => $post->getId(),
                'title' => $post->getTitle(),
                'content' => $post->getContent(),
                'timestamp' => $post->getTimestamp()->format('Y-m-d H:i:s'),
            ], Response::HTTP_CREATED);
        } catch (NotFoundHttpException|BadRequestHttpException $e) {
            return $this->json(['message' => $e->getMessage()], $e->getStatusCode());
        }
    }

    #[Route('/api/posts/{id}', name: 'post_update', methods: ['PUT'])]
    #[IsGranted('ROLE_USER')]
    public function update(Request $request, int $id): Response
    {
        try {
            $post = $this->postService->updatePost($id, json_decode($request->getContent(), true));

            return $this->json([
                'id' => $post->getId(),
                'title' => $post->getTitle(),
                'content' => $post->getContent(),
                'timestamp' => $post->getTimestamp()->format('Y-m-d H:i:s'),
            ]);
        } catch (NotFoundHttpException|BadRequestHttpException $e) {
            return $this->json(['message' => $e->getMessage()], $e->getStatusCode());
        }
    }

    #[Route('/api/posts/{id}', name: 'post_delete', methods: ['DELETE'])]
    #[IsGranted('ROLE_USER')]
    public function delete(int $id): Response
    {
        try {
            $this->postService->deletePost($id);

            return $this->json(null, Response::HTTP_NO_CONTENT);
        } catch (NotFoundHttpException|BadRequestHttpException $e) {
            return $this->json(['message' => $e->getMessage()], $e->getStatusCode());
        }
    }
}
