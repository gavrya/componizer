<?php
/**
 * Created by PhpStorm.
 * User: gavrya
 * Date: 10/29/15
 * Time: 11:03 AM
 */

namespace Gavrya\Gravitizer\Example;


use Gavrya\Gravitizer\Skeleton\GravitizerComponent;
use Gavrya\Gravitizer\Skeleton\GravitizerPlugin;

class ExamplePlugin extends GravitizerPlugin implements GravitizerComponent
{

    public function id()
    {
        return md5('example');
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

    public function init($lang)
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