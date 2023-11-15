<?php

namespace App\Repositories\Examples;

use App\Core\Repository\AbstractRepository;
use App\Models\Base\Company;
use Illuminate\Validation\Validator;

class ExampleRepository extends AbstractRepository
{

    protected static function getModel()
    {
        return Company::class;
    }

    protected function makeValidator(array $data, $isUpdate = false)
    {
        return Validator::make($data, $rules = [], $messages = [], $customAttributes = []);
    }
}
