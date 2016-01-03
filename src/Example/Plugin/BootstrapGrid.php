<?php
/**
 * Created by PhpStorm.
 * User: gavrya
 * Date: 10/29/15
 * Time: 11:03 AM
 */

namespace Gavrya\Componizer\Example\Plugin;


use Gavrya\Componizer\Component\AbstractPluginComponent;
use Gavrya\Componizer\Example\Widget\BootstrapColumn;
use Gavrya\Componizer\Example\Widget\BootstrapRow;


class BootstrapGrid extends AbstractPluginComponent
{

    private $id = '55963a8f';
    private $widgets = null;

    //-----------------------------------------------------
    // Constructor section
    //-----------------------------------------------------

    public function __construct()
    {
        $this->widgets = [new BootstrapRow(), new BootstrapColumn()];
    }

    //-----------------------------------------------------
    // ComponentInterface section
    //-----------------------------------------------------

    public function getId()
    {
        return $this->id;
    }

    public function getName()
    {
        return 'Bootstrap grid plugin';
    }

    public function getVersion()
    {
        return '1.0';
    }

    public function getInfo()
    {
        return 'Provides bootstrap grid layout';
    }

    public function hasAssets()
    {
        return false;
    }

    public function getAssetsDir()
    {
        return dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR . 'assets'. DIRECTORY_SEPARATOR . $this->id;
    }

    public function init($lang, $cacheDir)
    {
        // callback on plugin init
    }

    public function up()
    {
        // callback on plugin up
    }

    public function down()
    {
        // callback on plugin down
    }

    //-----------------------------------------------------
    // AbstractPluginComponent section
    //-----------------------------------------------------

    public function getWidgets()
    {
        return $this->widgets;
    }

}