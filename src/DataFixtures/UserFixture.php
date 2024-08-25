<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixture extends Fixture
{
    public const EMAIL = 'test@user.com';
    public const PASSWORD = '123123';
    public function __construct(private UserPasswordHasherInterface $passwordHasher)
    {
    }
    public function load(ObjectManager $manager): void
    {
        $user = new User();
        $user
            ->setId(1)
            ->setEmail(self::EMAIL)
            ->setStatus(User::STATUS_NEW)
        ;

        $password = $this->passwordHasher->hashPassword(
            $user,
            self::PASSWORD
        );

        $user->setPassword($password);
        $manager->persist($user);
        $manager->flush();
    }
}
