<?php

namespace App\Repositories;

use App\Core\Repository\AbstractRepository;
use App\Core\Repository\ValidatorMakeOptions;
use App\Models\User;

class UserRepository extends AbstractRepository
{

    protected static function getModel()
    {
        return User::class;
    }

    public function count()
    {
        return 0;
    }

    protected function makeValidator(array $data, ValidatorMakeOptions $validatorMakeOptions)
    {
        // TODO: Implement makeValidator() method.
    }
}
