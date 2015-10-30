<?php
/**
 * Created by PhpStorm.
 * User: gavrya
 * Date: 10/22/15
 * Time: 3:08 PM
 */

namespace Gavrya\Gravitizer\Skeleton;


abstract class GravitizerPlugin
{

    //-----------------------------------------------------
    // Widgets section
    //-----------------------------------------------------

    public function widgets()
    {
        return [];
    }

    public function countWidgets()
    {
        return 0;
    }

    public function hasWidgets()
    {
        return false;
    }

    public function hasWidget($widget)
    {
        return false;
    }

    //-----------------------------------------------------
    // Other component section
    //-----------------------------------------------------

}