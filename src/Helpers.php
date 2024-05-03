<?php

namespace Ensi\LaravelPhpRdKafka;

class Helpers
{
    public static function stringifyBoolean($value)
    {
        if ($value === true) {
            return "true";
        }

        if ($value === false) {
            return "false";
        }

        return $value;
    }
}
