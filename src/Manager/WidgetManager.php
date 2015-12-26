<?php
/**
 * Created by PhpStorm.
 * User: gavrya
 * Date: 12/8/15
 * Time: 8:43 PM
 */

namespace Gavrya\Componizer\Manager;


use Gavrya\Componizer\Asset\AssetsCollection;
use Gavrya\Componizer\Component\ComponentInterface;
use Gavrya\Componizer\Component\AbstractWidgetComponent;
use Gavrya\Componizer\Componizer;

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

    public function isWidgetValid($widget)
    {
        // check instance
        if (!($widget instanceof AbstractWidgetComponent)) {
            return false;
        }

        /** @var ComponentManager $componentManager */
        $componentManager = $this->componizer->resolve(ComponentManager::class);

        // validate component
        if (!$componentManager->isComponentValid($widget)) {
            return false;
        }

        // check widget editor assets
        if (!($widget->getEditorAssets() instanceof AssetsCollection)) {
            // todo: check if empty
            return false;
        }

        // check widget display assets
        if (!($widget->getDisplayAssets() instanceof AssetsCollection)) {
            // todo: check if empty
            return false;
        }

        return true;
    }

    //-----------------------------------------------------
    // Get/find section
    //-----------------------------------------------------

    public function getAllWidgets()
    {
        /** @var PluginManager $pluginManager */
        $pluginManager = $this->componizer->resolve(PluginManager::class);

        return $this->findWidgetComponents($pluginManager->getAllPlugins());
    }

    public function findWidget($widget)
    {
        return $this->findWidgetComponent($widget, $this->getAllWidgets());
    }

    //-----------------------------------------------------
    // Enabled/disabled section
    //-----------------------------------------------------

    public function getEnabledWidgets()
    {
        /** @var PluginManager $pluginManager */
        $pluginManager = $this->componizer->resolve(PluginManager::class);

        return $this->findWidgetComponents($pluginManager->getEnabledPlugins());
    }

    public function getDisabledWidgets()
    {
        /** @var PluginManager $pluginManager */
        $pluginManager = $this->componizer->resolve(PluginManager::class);

        return $this->findWidgetComponents($pluginManager->getDisabledPlugins());
    }

    public function findEnabledWidget($widget)
    {
        return $this->findWidgetComponent($widget, $this->getEnabledWidgets());
    }

    public function isWidgetEnabled($widget)
    {
        return $this->findEnabledWidget($widget) !== null;
    }

    //-----------------------------------------------------
    // Allow/Deny section
    //-----------------------------------------------------

    /**
     * Returns enabled and allowed widget for "editor content" rendering.
     *
     * @return array
     */
    public function getAllowedWidgets()
    {
        // todo: implement using SettingsManager in future
        return $this->getEnabledWidgets();
    }

    public function getDeniedWidgets()
    {
        // todo: implement using SettingsManager in future
        return $this->getDisabledWidgets();
    }

    public function findAllowedWidget($widget)
    {
        return $this->findWidgetComponent($widget, $this->getAllowedWidgets());
    }

    public function isWidgetAllowed($widget)
    {
        return $this->findAllowedWidget($widget) !== null;
    }

    //-----------------------------------------------------
    // Internal methods section
    //-----------------------------------------------------

    private function findWidgetComponents(array $plugins)
    {
        $widgets = [];

        /** @var \Gavrya\Componizer\Skeleton\ComponizerPlugin $plugin */
        foreach ($plugins as $plugin) {
            /** @var ComponentInterface|AbstractWidgetComponent $widget */
            foreach ($plugin->widgets() as $widget) {
                $widgets[$widget->getId()] = $widget;
            }
        }

        return $widgets;
    }

    private function findWidgetComponent($widget, array $widgets)
    {
        $widgetId = null;

        if (is_string($widget)) {
            $widgetId = $widget;
        } elseif ($widget instanceof AbstractWidgetComponent) {
            $widgetId = $widget->getId();
        } else {
            return null;
        }

        return isset($widgets[$widgetId]) ? $widgets[$widgetId] : null;
    }

}