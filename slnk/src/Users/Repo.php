<?php declare(strict_types=1);

namespace App\Users;

use MongoDB\Collection;

class Repo extends \App\Mongo\Repo
{

    public function proxyUser(string $id): ProxyUser
    {
        return new ProxyUser($id, function() use($id) {
            return $this->get($id);
        });
    }

    public function findByToken(string $token): ?User
    {
        $result = $this->getCollection()->findOne([
            'token' => $token,
        ]);

        return $result ? $this->map($result) : null;
    }

    protected function getCollection(): Collection
    {
        return $this->db->users;
    }

    protected function map(object $doc): User
    {
        return new User(
            (string)$doc->_id,
            $doc->name
        );
    }

    /**
     * @param IUser $user
     * @return array
     */
    protected function pam($user): array
    {
        return [
            'name' => $user->getName(),
        ];
    }

}