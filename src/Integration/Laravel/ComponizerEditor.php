<?php
/**
 * Created by PhpStorm.
 * User: gavrya
 * Date: 1/4/16
 * Time: 9:05 PM
 */

namespace Gavrya\Componizer\Integration\Laravel;

use Illuminate\Support\Facades\Facade;

/**
 * Class ComponizerEditor
 *
 * @method \Gavrya\Componizer\ComponizerConfig getConfig()
 * @method \Gavrya\Componizer\Manager\PluginManager getPluginManager()
 * @method \Gavrya\Componizer\Manager\WidgetManager getWidgetManager()
 * @method \Gavrya\Componizer\Content\ContentProcessor getContentProcessor()
 *
 * @package Gavrya\Componizer\Integration\Laravel
 */
class ComponizerEditor extends Facade
{

    protected static function getFacadeAccessor() { return 'componizer'; }

}