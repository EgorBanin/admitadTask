<?php declare(strict_types=1);

namespace App\Links;

use App\Users\IUser;
use App\Users\Repo as UserRepo;
use App\Users\User;
use MongoDB\BSON\ObjectId;
use MongoDB\Collection;
use MongoDB\Driver\Exception\WriteException;

class Repo extends \App\Mongo\Repo
{

    private UserRepo $userRepo;

    public function __construct(\MongoDB\Database $db, UserRepo $userRepo)
    {
        parent::__construct($db);
        $this->userRepo = $userRepo;
    }

    public static function createLink(string $url, User $author): Link
    {
        return new Link(null, Link::generateKey(), $url, $author, time());
    }

    public function findByKey(string $key): ?Link
    {
        try {
            $result = $this->getCollection()->findOne([
                'key' => $key,
            ]);
        } catch (\Exception $e) {
            throw new \App\Mongo\Exception(
                'Не удалось найти ссылку.',
                \App\Mongo\Exception::CODE_FIND_ERR
            );
        }

        return $result ? $this->map($result) : null;
    }

    public function hardInsert(Link $link, int $retry): string
    {
        $result = null;
        while (($retry--) > 0) {
            try {
                $result = $this->getCollection()->insertOne($this->pam($link));
                break;
            } catch (WriteException $e) {
                $link->regenerateKey();
                continue;
            } catch (\Exception $e) {
                throw new \App\Mongo\Exception(
                    'Не удалось вставить ссылку.',
                    \App\Mongo\Exception::CODE_INSERT_ERR
                );
            }
        }

        if (!$result) {
            throw new \App\Mongo\Exception(
                'Не удалось вставить ссылку.',
                \App\Mongo\Exception::CODE_INSERT_ERR
            );
        }

        return (string)$result->getInsertedId();
    }

    public function getStat(?IUser $user, ?int $day): Stat
    {
        $query = [];
        if ($user) {
            $query['userId'] = new ObjectId($user->getId());
        }
        if ($day) {
            $query['ct'] = [
                '$gte' => $day,
                '$lt' => $day + (24 * 60 * 60),
            ];
        }
        try {
            $count = $this->getCollection()->countDocuments($query);
        } catch (\Exception $e) {
            throw new \App\Mongo\Exception(
                'Не удалось посчитать ссылки.',
                \App\Mongo\Exception::CODE_COUNT_ERR
            );
        }

        return new Stat($count, $user, $day);
    }

    protected function getCollection(): Collection
    {
        return $this->db->links;
    }

    protected function map(object $doc): Link
    {
        return new Link(
            (string)$doc->_id,
            $doc->key,
            $doc->url,
            $this->userRepo->proxyUser((string)$doc->userId),
            $doc->ct
        );
    }

    /**
     * @param Link $link
     * @return array
     */
    protected function pam($link): array
    {
        return [
            'key' => $link->getKey(),
            'url' => $link->getUrl(),
            'userId' => new ObjectId($link->getAuthor()->getId()),
            'ct' => $link->getCt(),
        ];
    }

}