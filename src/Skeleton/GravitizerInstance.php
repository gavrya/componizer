<?php
/**
 * Created by PhpStorm.
 * User: gavrya
 * Date: 10/23/15
 * Time: 12:26 AM
 */

namespace Gavrya\Gravitizer\Skeleton;


interface GravitizerInstance
{
    public static function setup($config);

    public static function instance();

    public function version();

    public function config();

    public function pluginManager();

    public function widgetManager();

}