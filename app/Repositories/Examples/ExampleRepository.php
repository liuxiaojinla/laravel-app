<?php

namespace App\Repositories\Examples;

use App\Models\Base\Company;
use App\Repositories\AbstractRepository;
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
