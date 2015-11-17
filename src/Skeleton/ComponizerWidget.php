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

    public function makeDisplayContent(callable $parser, array $properties, $contentType, $content = null)
    {
        return '';
    }

}