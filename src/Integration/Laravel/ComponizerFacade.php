<?php
/**
 * Created by PhpStorm.
 * User: gavrya
 * Date: 1/4/16
 * Time: 9:05 PM
 */

namespace Integration\Laravel;

use Illuminate\Support\Facades\Facade;

class ComponizerFacade extends Facade
{

    protected static function getFacadeAccessor() { return 'componizer'; }

}