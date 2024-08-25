<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsController]
class GetUser extends AbstractController
{
    public function __construct(
        private UserRepository $repository,
        private UserPasswordHasherInterface $passwordHasher
    ) {
    }

    public function __invoke(Request $request): Response
    {
        $data = $request->toArray();
        $user = $this->repository->findOneByEmail($data['email'] ?? null);

        if (!$user || !$this->passwordHasher->isPasswordValid($user, $data['password'])) {
            throw new NotFoundHttpException('User not found');
        }

        return $this->json(['id' => $user->getId()]);
    }
}
