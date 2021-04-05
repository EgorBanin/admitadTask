<?php declare(strict_types=1);

namespace App\Users;

use App\Mongo\IObj;

interface IUser extends IObj
{

    public function getName(): string;

}