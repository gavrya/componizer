<?php
/**
 * Created by PhpStorm.
 * User: gavrya
 * Date: 10/29/15
 * Time: 11:03 AM
 */

namespace Gavrya\Componizer\Plugin;


use Gavrya\Componizer\Skeleton\ComponizerComponent;
use Gavrya\Componizer\Skeleton\ComponizerPlugin;

class ExamplePlugin extends ComponizerPlugin implements ComponizerComponent
{

    public function id()
    {
        return '1a79a4d60de6718e8e5b326e338ae533';
    }

    public function name()
    {
        return 'Example plugin';
    }

    public function version()
    {
        return '1.0';
    }

    public function info()
    {
        return 'Info about example plugin';
    }

    public function hasAssets()
    {
        return false;
    }

    public function assetsDir()
    {
        return null;
    }

    public function init($lang, $cacheDir)
    {
        echo 'example plugin init' . PHP_EOL;
    }

    public function up()
    {
        echo 'example plugin up' . PHP_EOL;
    }

    public function down()
    {
        echo 'example plugin down' . PHP_EOL;
    }


}