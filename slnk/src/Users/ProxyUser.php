<?php declare(strict_types=1);

namespace App\Users;

class ProxyUser implements IUser
{

    private string $id;

    /** @var callable */
    private $getUser;

    private ?User $user;

    public function __construct(string $id, callable $getUser)
    {
        $this->id = $id;
        $this->getUser = $getUser;
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getName(): string
    {
        $this->getUser()->getName();
    }


    private function getUser(): User
    {
        if (!$this->user) {
            $this->user = ($this->getUser)();
        }

        return $this->user;
    }
}