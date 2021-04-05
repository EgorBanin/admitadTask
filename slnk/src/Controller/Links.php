<?php declare(strict_types=1);

namespace App\Controller;

use App\Links\Exception;
use App\Links\Repo as LinksRepo;
use App\Users\Repo as UsersRepo;
use App\Users\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Exception\JsonException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use App\Users\Service as UserService;

class Links extends AbstractController
{

    private UserService $userService;

    private LinksRepo $linksRepo;

    private UsersRepo $usersRepo;

    public function __construct(
        UserService $userService,
        LinksRepo $linksRepo,
        UsersRepo $usersRepo
    )
    {
        $this->userService = $userService;
        $this->linksRepo = $linksRepo;
        $this->usersRepo = $usersRepo;
    }

    public function create(Request $rq): Response
    {
        $user = $this->userService->authenticate($rq);
        if (!$user) {
            throw new HttpException(403, 'Доступ запрещён.');
        }

        try {
            $linkArr = $rq->toArray();
        } catch (JsonException $e) {
            throw new HttpException(400, 'Некорректный запрос.', $e);
        }

        try {
            $link = LinksRepo::createLink((string) ($linkArr['url']?? ''), $user);
        } catch (Exception $e) {
            throw new HttpException(400, 'Некорректный запрос.', $e);
        }

        try {
            $this->linksRepo->hardInsert($link, 3);
        } catch (Exception $e) {
            throw new HttpException(500, 'Не удалось создать ссылку.', $e);
        }

        return $this->json($link);
    }

    public function find(string $key): Response
    {
        $link = $this->linksRepo->findByKey($key);
        if (!$link) {
            throw new HttpException(404, "Не найдена ссылка \"$key\".");
        }

        return $this->redirect($link->getUrl());
    }

    public function stat(Request $rq)
    {
        $dateStr = $rq->query->get('date');
        if ($dateStr) {
            try {
                $date = new \DateTime($dateStr);
            } catch (\Exception $e) {
                throw new HttpException(400, "Неверный формат даты \"$dateStr\"", $e);
            }
            $day = $date->getTimestamp();
        } else {
            $day = null;
        }

        $userId = $rq->query->get('user');
        if ($userId) {
            try {
                /** @var User $user */
                $user = $this->usersRepo->get($userId);
            } catch (\App\Mongo\Exception $e) {
                throw new HttpException(400, "Не найден пользователь #$userId.");
            }
        } else {
            $user = null;
        }

        $stat = $this->linksRepo->getStat($user, $day);

        return $this->json($stat);
    }
}