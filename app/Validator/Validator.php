<?php

namespace App\Validator;

use \Respect\Validation\Validator as Respect;
use \Respect\Validation\Exceptions\NestedValidationException;
use App\Helpers\Session;
use Slim\Http\Request;

/**
 * Class Validator - for validating data
 *
 * @package App\Validator
 */
class Validator
{
    /**
     * @var array
     */
    protected $errors;

    /**
     * Validating data
     *
     * @param Request $request
     *
     * @param array $rules - validation rules
     *
     * @return $this
     */
    public function validate($request, array $rules)
    {
        foreach ($rules as $filed => $rule)
        {
            try {
                $rule->setName(ucfirst($filed))->assert($request->getParam($filed));
            }
            catch (NestedValidationException $e)
            {
                $this->errors[$filed] = $e->getMessages();
            }
        }

        Session::set("errors",$this->errors);
        return $this;
    }

    /**
     * Check errors
     *
     * @return bool
     */
    public function failed()
    {
        return !empty($this->errors);
    }
}