<?php

namespace App\Tests\Api;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;

class UserTest extends ApiTestCase
{
    protected function setUp(): void
    {
        self::bootKernel();

        $application = new Application(self::$kernel);
        $application->setAutoExit(false);

        // Очистка и загрузка фикстур
        $application->run(new ArrayInput([
            'command' => 'doctrine:fixtures:load',
            '--no-interaction' => true,
            '--env' => 'test',
            '--purge-with-truncate' => true,  // Используем TRUNCATE для очистки
        ]));
    }
    public function testCreateUser(): void
    {
        $email =  'test@user.com';
        static::createClient()->request('POST', 'https://user.localhost/registration', [
            'json' => [
                'email' => $email,
                'password' => '123',
                'confirmPassword' => '123'
            ],
            'headers' => [
                'Content-Type' => 'application/ld+json',
            ]
        ]);

        $this->assertResponseStatusCodeSame(201);
        $userRepository = self::getContainer()->get(UserRepository::class);
        $this->assertEquals($userRepository->findOneByEmail($email)?->getEmail(), $email);
    }

    public function testFindByLoginPassword(): void
    {
        $email =  'test@user.com';
        $result = static::createClient()->request('POST', 'https://user.localhost/find-by-login-password', [
            'json' => [
                'email' => $email,
                'password' => '123123'
            ],
            'headers' => [
                'Content-Type' => 'application/ld+json',
            ]
        ]);
        $this->assertResponseStatusCodeSame(200);
        $data = $result->toArray();
        $this->assertArrayHasKey('id', $data);
        $this->assertIsNumeric($data['id']);
    }
}
