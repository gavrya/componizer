<?php
/**
 * Created by PhpStorm.
 * User: gavrya
 * Date: 11/8/15
 * Time: 12:03 PM
 */

namespace Gavrya\Componizer\Skeleton;


abstract class ComponizerWidget
{

    //-----------------------------------------------------
    // Assets section
    //-----------------------------------------------------

    abstract public function editorAssets();

    abstract public function displayAssets();

    //-----------------------------------------------------
    // Make display content section
    //-----------------------------------------------------

    public function makeDisplayContent(callable $parser, array $properties, $contentType, $content = null)
    {
        return '';
    }

}