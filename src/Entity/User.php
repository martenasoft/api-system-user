<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Controller\GetUser;
use App\Repository\UserRepository;
use App\State\RegistrationStateProcessor;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Expression;
use Symfony\Component\Validator\Constraints\NotBlank;

#[ApiResource(
    operations: [
        new Post(
            name: 'get-user',
            controller: GetUser::class,
            uriTemplate: '/find-by-login-password'
        )
    ],

    normalizationContext: ['groups' => ['find-user:read']],
    denormalizationContext: ['groups' => ['find-user:write']],
    validationContext: ['groups' => ['find-user:write']]
)]
#[ApiResource(operations: [
    new Get(),
    new Delete(),
    new Patch(),
    new GetCollection(),

    new Post(
        processor: RegistrationStateProcessor::class,
        uriTemplate: '/registration',
        status: 201
    )
],
    normalizationContext: ['groups' => ['user:read']],
    denormalizationContext: ['groups' => ['user:write']],
    validationContext: ['groups' => ['user:write']],
)]
#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[ORM\HasLifecycleCallbacks]
class User implements PasswordAuthenticatedUserInterface
{
    public const STATUS_NEW = 1;
    public const STATUS_ACTIVE = 2;
    public const STATUS_DELETED = 3;

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: "AUTO")]
    #[ORM\Column(type: "integer")]
    #[Groups(['find-user:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255, unique: true)]
    #[Email]
    #[Groups(['user:read', 'user:write', 'find-user:write'])]
    private ?string $email = null;

    #[ORM\Column(length: 255)]
    #[NotBlank]
    #[Groups(['user:read', 'user:write', 'find-user:write'])]
    private ?string $password = null;

    #[ORM\Column(type: Types::SMALLINT, options: ["default" => self::STATUS_NEW])]
    private ?int $status = null;

    #[ORM\Column]
    #[Groups(['user:read'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['user:read'])]
    private ?\DateTimeImmutable $updatedAt = null;

    #[NotBlank]
    #[Expression(expression: "this.getPassword() === this.getConfirmPassword()", message: "Passwords do not match.")]
    #[Groups(['user:write'])]
    private ?string $confirmPassword = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): static
    {
        $this->id = $id;
        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }
    #[ORM\PrePersist]
    public function setDefaultStatus(): void
    {
        $this->status = self::STATUS_NEW;
    }
    public function getStatus(): ?int
    {
        return $this->status;
    }
    public function setStatus(?int $status = null): static
    {
        $this->status = $status;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }
    #[ORM\PrePersist]
    public function setCreatedAt(): static
    {
        $this->createdAt = new \DateTimeImmutable('now');

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }
    #[ORM\PreUpdate]
    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getConfirmPassword(): ?string
    {
        return $this->confirmPassword;
    }

    public function setConfirmPassword(string $confirmPassword): static
    {
        $this->confirmPassword = $confirmPassword;

        return $this;
    }
}
