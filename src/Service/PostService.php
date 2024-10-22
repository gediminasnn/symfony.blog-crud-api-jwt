<?php

namespace App\Service;

use App\Entity\Post;
use App\Interfaces\PostServiceInterface;
use App\Interfaces\PostRepositoryInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class PostService implements PostServiceInterface
{
    private PostRepositoryInterface $postRepository;
    private PaginatorInterface $paginator;
    private ValidatorInterface $validator;

    public function __construct(
        PostRepositoryInterface $postRepository,
        PaginatorInterface $paginator,
        ValidatorInterface $validator
    ) {
        $this->postRepository = $postRepository;
        $this->paginator = $paginator;
        $this->validator = $validator;
    }

    public function createPost(array $data): Post
    {
        $post = new Post();
        $post->setTitle($data['title'] ?? '');
        $post->setContent($data['content'] ?? '');
        $post->setTimestamp(new \DateTime());

        $errors = $this->validator->validate($post);

        if (count($errors) > 0) {
            throw new BadRequestHttpException((string)$errors);
        }

        $this->postRepository->add($post, true);

        return $post;
    }

    public function updatePost(int $id, array $data): Post
    {
        $post = $this->postRepository->find($id);

        if (!$post) {
            throw new NotFoundHttpException('Post not found.');
        }

        $post->setTitle($data['title'] ?? $post->getTitle());
        $post->setContent($data['content'] ?? $post->getContent());

        $errors = $this->validator->validate($post);

        if (count($errors) > 0) {
            throw new BadRequestHttpException((string)$errors);
        }

        $this->postRepository->add($post, true);

        return $post;
    }

    public function deletePost(int $id): void
    {
        $post = $this->postRepository->find($id);

        if (!$post) {
            throw new NotFoundHttpException('Post not found.');
        }

        $this->postRepository->remove($post, true);
    }

    public function getPost(int $id): ?Post
    {
        $post = $this->postRepository->find($id);

        if (!$post) {
            throw new NotFoundHttpException('Post not found.');
        }

        return $post;
    }

    public function getAllPosts(int $page, int $limit): array
    {
        $queryBuilder = $this->postRepository->createQueryBuilder('p')
            ->orderBy('p.timestamp', 'DESC');

        $pagination = $this->paginator->paginate(
            $queryBuilder,
            $page,
            $limit
        );

        $items = [];
        foreach ($pagination->getItems() as $post) {
            $items[] = [
                'id' => $post->getId(),
                'title' => $post->getTitle(),
                'content' => $post->getContent(),
                'timestamp' => $post->getTimestamp()->format('Y-m-d H:i:s'),
            ];
        }

        return [
            'items' => $items,
            'total' => $pagination->getTotalItemCount(),
            'page' => $pagination->getCurrentPageNumber(),
            'limit' => $pagination->getItemNumberPerPage(),
        ];
    }
}
