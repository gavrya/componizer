<?php
/**
 * Created by PhpStorm.
 * User: gavrya
 * Date: 10/29/15
 * Time: 11:03 AM
 */

namespace Gavrya\Componizer\Component\Plugin;


use Gavrya\Componizer\Component\Widget\ExampleWidget;
use Gavrya\Componizer\Skeleton\ComponizerComponent;
use Gavrya\Componizer\Skeleton\ComponizerPlugin;

class ExamplePlugin extends ComponizerPlugin implements ComponizerComponent
{

    private $id = '55963a8f';
    private $widgets = null;

    //-----------------------------------------------------
    // Create/init section
    //-----------------------------------------------------

    public function __construct()
    {
        $this->widgets = [new ExampleWidget()];
    }

    //-----------------------------------------------------
    // ComponizerComponent section
    //-----------------------------------------------------

    public function id()
    {
        return $this->id;
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
        return true;
    }

    public function assetsDir()
    {
        return dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . $this->id;
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

    //-----------------------------------------------------
    // ComponizerPlugin section
    //-----------------------------------------------------

    public function widgets()
    {
        return $this->widgets;
    }


}