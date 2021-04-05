<?php declare(strict_types=1);

namespace App\Mongo;

use MongoDB\BSON\ObjectId;
use MongoDB\Collection;

abstract class Repo
{

    protected \MongoDB\Database $db;

    public function __construct(\MongoDB\Database $db)
    {
        $this->db = $db;
    }

    public function get(string $id): object
    {
        try {
            $result = $this->getCollection()->findOne([
                '_id' => new ObjectId($id),
            ]);
        } catch (\Exception $e) {
            throw new Exception("Не найден документ #$id.", Exception::CODE_FIND_ERR, $e);
        }
        if (!$result) {
            throw new Exception("Не найден документ #$id.", Exception::CODE_GET_ERR);
        }

        return $this->map($result);
    }

    public function save(IObj $obj)
    {
        if ($obj->getId()) {
            $this->update($obj);
        } else {
            (function (string $id) {
                $this->id = $id;
            })->bindTo($obj, $obj)($this->insert($obj));
        }
    }

    public function update(IObj $obj)
    {
        try {
            $this->getCollection()->updateOne(
                ['_id' => new ObjectId($obj->getId())],
                [
                    '$set' => $this->pam($obj)
                ]
            );
        } catch (\Exception $e) {
            throw new Exception(
                sprintf('Не найден обновить документ %s #%s.', get_class($obj), $obj->getId()),
                Exception::CODE_UPDATE_ERR,
                $e
            );
        }
    }

    public function insert(IObj $obj): string
    {
        try {
            $result = $this->getCollection()->insertOne($this->pam($obj));
        } catch (\Exception $e) {
            throw new Exception(
                sprintf('Не удалось вставить объект %s.', \get_class($obj)),
                Exception::CODE_INSERT_ERR
            );
        }

        return (string)$result->getInsertedId();
    }

    abstract protected function getCollection(): Collection;

    abstract protected function map(object $doc): object;

    abstract protected function pam($obj): array;
}