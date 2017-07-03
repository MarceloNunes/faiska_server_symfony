<?php

namespace AppBundle\Exception\Http;

class BadRequest extends \Exception
{
    const BLANK_VALUE                 = 'BLANK_VALUE';
    const NULL_VALUE                  = 'NULL_VALUE';
    const INVALID_FORMAT              = 'INVALID_FORMAT';
    const BELOW_MINIMUM_VALUE         = 'BELOW_MINIMUM_VALUE';
    const ABOVE_MAXIMUM_VALUE         = 'ABOVE_MAXIMUM_VALUE';
    const TOO_LONG                    = 'TOO_LONG';
    const TOO_SHORT                   = 'TOO_SHORT';
    const DATE_IN_THE_PAST            = 'DATE_IN_THE_PAST';
    const DATE_IN_THE_FUTURE          = 'DATE_IN_THE_FUTURE';
    const UNIQUE_KEY_CONSTRAINT_ERROR = 'UNIQUE_KEY_CONSTRAINT_ERROR';

    /** @var array */
    private $errors = array();

    /**
     * @param $field
     * @param $code
     * @return $this
     */
    public function addError($field, $code)
    {
        $this->errors[] = array(
            'field' => $field,
            'code'  => $code
        );

        return $this;
    }

    public function getErrors () {
        return $this->errors;
    }
}