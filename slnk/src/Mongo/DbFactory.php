<?php declare(strict_types=1);

namespace App\Mongo;

class DbFactory
{

    public static function init(
        string $uri,
        array $uriOptions,
        array $driverOptions,
        string $database
    ): \MongoDB\Database
    {
        try {
            $mongo = new \MongoDB\Client($uri, $uriOptions, $driverOptions);
        } catch (\MongoDB\Exception\Exception $e) {
            throw new \Exception(
                'Не удалось подключиться к mongodb.',
                0,
                $e
            );
        }

        try {
            $db = $mongo->selectDatabase($database);
        } catch (\MongoDB\Exception\Exception $e) {
            throw new \Exception(
                "Не удалось выбрать базу $database.",
                0,
                $e
            );
        }

        return $db;
    }

}