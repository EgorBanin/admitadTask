<?php declare(strict_types=1);

namespace App\Mongo;

class Exception extends \Exception
{
    public const CODE_GET_ERR = 1;
    public const CODE_FIND_ERR = 2;
    public const CODE_INSERT_ERR = 3;
    public const CODE_UPDATE_ERR = 4;
    public const CODE_COUNT_ERR = 5;
}