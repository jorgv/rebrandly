<?php
namespace Jorgv\Rebrandly\Facades;

use Illuminate\Support\Facades\Facade;

class Rebrandly extends Facade
{
    /**
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'rebrandly';
    }
}
