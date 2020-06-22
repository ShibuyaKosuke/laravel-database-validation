<?php

namespace ShibuyaKosuke\LaravelDatabaseValidator\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * Class ValidationRule
 * @package ShibuyaKosuke\LaravelDatabaseValidator\Facades
 */
class ValidationRule extends Facade
{
    /**
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'rules';
    }
}