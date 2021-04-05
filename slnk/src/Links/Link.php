<?php declare(strict_types=1);

namespace App\Links;

use App\Mongo\IObj;
use App\Users\IUser;

class Link implements IObj, \JsonSerializable
{

    private const KEY_EPOCH = 1617567540; // 2021-01-01

    private ?string $id;

    private string $key;

    private string $url;

    private IUser $author;

    private int $ct;

    public function __construct(
        ?string $id,
        string $key,
        string $url,
        IUser $author,
        int $ct
    )
    {
        $this->id = $id;
        $this->key = $key;
        $filteredUrl = \filter_var($url, \FILTER_VALIDATE_URL);
        if (!$filteredUrl) {
            throw new Exception(
                "URL \"$url\" невалидный.",
                Exception::CODE_INVALID_URL
            );
        }
        $this->url = $filteredUrl;
        $this->author = $author;
        $this->ct = $ct;
    }

    public static function generateKey(): string
    {
        /* очень наивная реализация для тестового задания */

        $time = time() - self::KEY_EPOCH;

        return
            base_convert(rand(36, 1295), 10, 36) // 2 символа
             . base_convert($time, 10, 36);
    }

    public function regenerateKey()
    {
        $this->key = self::generateKey();
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getAuthor(): IUser
    {
        return $this->author;
    }

    public function getCt(): int
    {
        return $this->ct;
    }

    public function jsonSerialize()
    {
        return [
            'key' => $this->key,
            'url' => $this->url,
        ];
    }

}