<?php
/**
 * Created by PhpStorm.
 * User: gavrya
 * Date: 12/8/15
 * Time: 8:43 PM
 */

namespace Gavrya\Componizer;


use Gavrya\Componizer\Skeleton\ComponizerAssets;
use Gavrya\Componizer\Skeleton\ComponizerComponent;
use Gavrya\Componizer\Skeleton\ComponizerWidget;

class WidgetManager
{

    // Componizer
    private $componizer = null;

    //-----------------------------------------------------
    // Instance creation/init section
    //-----------------------------------------------------

    public function __construct(Componizer $componizer)
    {
        $this->componizer = $componizer;
    }

    //-----------------------------------------------------
    // Validation section
    //-----------------------------------------------------

    public function isValid($widget)
    {
        // check instance
        if (!($widget instanceof ComponizerComponent && $widget instanceof ComponizerWidget)) {
            return false;
        }

        /** @var ComponentManager $componentManager */
        $componentManager = $this->componizer->resolve(ComponentManager::class);

        // validate component
        if (!$componentManager->isValid($widget)) {
            return false;
        }

        // check widget editor assets
        if (!($widget->editorAssets() instanceof ComponizerAssets)) {
            // todo: check if empty
            return false;
        }

        // check widget display assets
        if (!($widget->displayAssets() instanceof ComponizerAssets)) {
            // todo: check if empty
            return false;
        }

        return true;
    }

    //-----------------------------------------------------
    // Get/find section
    //-----------------------------------------------------

    public function all()
    {
        /** @var PluginManager $pluginManager */
        $pluginManager = $this->componizer->resolve(PluginManager::class);

        return $this->findWidgets($pluginManager->all());
    }

    public function find($widget)
    {
        return $this->findWidget($widget, $this->all());
    }

    //-----------------------------------------------------
    // Enabled/disabled section
    //-----------------------------------------------------

    public function enabled()
    {
        /** @var PluginManager $pluginManager */
        $pluginManager = $this->componizer->resolve(PluginManager::class);

        return $this->findWidgets($pluginManager->enabled());
    }

    public function disabled()
    {
        /** @var PluginManager $pluginManager */
        $pluginManager = $this->componizer->resolve(PluginManager::class);

        return $this->findWidgets($pluginManager->disabled());
    }

    public function findEnabled($widget)
    {
        return $this->findWidget($widget, $this->enabled());
    }

    public function isEnabled($widget)
    {
        return $this->findEnabled($widget) !== null;
    }

    //-----------------------------------------------------
    // Allow/Deny section
    //-----------------------------------------------------

    /**
     * Returns enabled and allowed widget for "editor content" rendering.
     *
     * @return array
     */
    public function allowed()
    {
        // todo: implement using SettingsManager in future
        return $this->enabled();
    }

    public function denied()
    {
        // todo: implement using SettingsManager in future
        return $this->disabled();
    }

    public function findAllowed($widget)
    {
        return $this->findWidget($widget, $this->allowed());
    }

    public function isAllowed($widget)
    {
        return $this->findAllowed($widget) !== null;
    }

    //-----------------------------------------------------
    // Internal methods section
    //-----------------------------------------------------

    private function findWidgets(array $plugins)
    {
        $widgets = [];

        /** @var \Gavrya\Componizer\Skeleton\ComponizerPlugin $plugin */
        foreach ($plugins as $plugin) {
            /** @var ComponizerComponent|ComponizerWidget $widget */
            foreach ($plugin->widgets() as $widget) {
                $widgets[$widget->id()] = $widget;
            }
        }

        return $widgets;
    }

    private function findWidget($widget, array $widgets)
    {
        $widgetId = null;

        if (is_string($widget)) {
            $widgetId = $widget;
        } elseif ($widget instanceof ComponizerWidget && $widget instanceof ComponizerComponent) {
            $widgetId = $widget->id();
        } else {
            return null;
        }

        return isset($widgets[$widgetId]) ? $widgets[$widgetId] : null;
    }

}