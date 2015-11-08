<?php
/**
 * Created by PhpStorm.
 * User: gavrya
 * Date: 11/8/15
 * Time: 12:03 PM
 */

namespace Gavrya\Componizer\Skeleton;


use Closure;

abstract class ComponizerWidget
{

    //-----------------------------------------------------
    // Widget section
    //-----------------------------------------------------

    public function parse(Closure $parserHelper, array $jsonData, $contentType, $content = null)
    {
        echo __FUNCTION__;
    }

}