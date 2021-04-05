<?php declare(strict_types=1);

namespace App\Users;

use Symfony\Component\HttpFoundation\Request;

class Service
{

    private Repo $repo;

    public function __construct(Repo $repo)
    {
        $this->repo = $repo;
    }

    public function authenticate(Request $rq): ?User
    {
        $matches = [];
        preg_match(
            '/^Bearer\s(?<token>.+)$/',
            $rq->headers->get('Authorization', ''),
            $matches
        );
        $token = $matches['token'] ?? null;
        if (!$token) {
            return null;
        }

        return $this->repo->findByToken($token);
    }

}