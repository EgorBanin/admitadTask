<?php declare(strict_types=1);

namespace App\Links;

use App\Users\IUser;

class Stat implements \JsonSerializable
{

    private int $count;

    private ?IUser $user;

    private ?int $day;

    public function __construct(int $count, ?IUser $user, ?int $day)
    {
        $this->count = $count;
        $this->user = $user;
        $this->day = $day;
    }

    public function jsonSerialize()
    {
        return [
            'count' => $this->count,
            'user' => $this->user? $this->user->getName() : null,
            'date' => $this->day?
                (new \DateTime)->setTimestamp($this->day * 24 * 60 * 60)->format(\DATE_ISO8601)
                : null,
        ];
    }


}