<?php
/**
 * Created by PhpStorm.
 * User: gavrya
 * Date: 1/4/16
 * Time: 9:05 PM
 */

namespace Gavrya\Componizer\Integration\Laravel;

use Gavrya\Componizer\Componizer;
use Illuminate\Support\Facades\Facade;

/**
 * Class ComponizerEditor
 *
 * @method \Gavrya\Componizer\ComponizerConfig getConfig()
 * @method \Gavrya\Componizer\Managers\PluginManager getPluginManager()
 * @method \Gavrya\Componizer\Managers\WidgetManager getWidgetManager()
 * @method \Gavrya\Componizer\Processing\ContentProcessor getContentProcessor()
 *
 * @package Gavrya\Componizer\Integration\Laravel
 */
class ComponizerEditor extends Facade
{

    protected static function getFacadeAccessor() { return Componizer::class; }

}