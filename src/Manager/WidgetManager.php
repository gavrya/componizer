<?php
/**
 * Created by PhpStorm.
 * User: gavrya
 * Date: 12/8/15
 * Time: 8:43 PM
 */

namespace Gavrya\Componizer\Manager;


use Gavrya\Componizer\Asset\AssetsCollection;
use Gavrya\Componizer\Component\AbstractPluginComponent;
use Gavrya\Componizer\Component\AbstractWidgetComponent;
use Gavrya\Componizer\Componizer;

/**
 * Class WidgetManager is used for widget management.
 *
 * @package Gavrya\Componizer\Manager
 */
class WidgetManager
{

    /**
     * @var Componizer
     */
    private $componizer = null;

    //-----------------------------------------------------
    // Constructor section
    //-----------------------------------------------------

    /**
     * WidgetManager constructor.
     *
     * @param Componizer $componizer
     */
    public function __construct(Componizer $componizer)
    {
        $this->componizer = $componizer;
    }

    //-----------------------------------------------------
    // Validation section
    //-----------------------------------------------------

    /**
     * Tells if widget valid.
     *
     * @param AbstractWidgetComponent $widget
     * @return bool
     */
    public function isWidgetValid($widget)
    {
        if (!($widget instanceof AbstractWidgetComponent)) {
            return false;
        }

        /** @var ComponentManager $componentManager */
        $componentManager = $this->componizer->resolve(ComponentManager::class);

        if (!$componentManager->isComponentValid($widget)) {
            return false;
        }

        if (!($widget->getEditorAssets() instanceof AssetsCollection)) {
            return false;
        }

        if (!($widget->getDisplayAssets() instanceof AssetsCollection)) {
            return false;
        }

        return true;
    }

    //-----------------------------------------------------
    // Get/find section
    //-----------------------------------------------------

    /**
     * Returns all widgets.
     *
     * @return AbstractWidgetComponent[]
     */
    public function getAllWidgets()
    {
        /** @var PluginManager $pluginManager */
        $pluginManager = $this->componizer->resolve(PluginManager::class);

        return $this->findWidgetComponents($pluginManager->getAllPlugins());
    }

    /**
     * Finds widget by id or instance.
     *
     * @param AbstractWidgetComponent|string $widget
     * @return AbstractWidgetComponent|null
     */
    public function findWidget($widget)
    {
        return $this->findWidgetComponent($widget, $this->getAllWidgets());
    }

    //-----------------------------------------------------
    // Enabled/disabled section
    //-----------------------------------------------------

    /**
     * Returns all enabled widgets.
     *
     * @return AbstractWidgetComponent[]
     */
    public function getEnabledWidgets()
    {
        /** @var PluginManager $pluginManager */
        $pluginManager = $this->componizer->resolve(PluginManager::class);

        return $this->findWidgetComponents($pluginManager->getEnabledPlugins());
    }

    /**
     * Returns all disabled widgets.
     *
     * @return AbstractWidgetComponent[]
     */
    public function getDisabledWidgets()
    {
        /** @var PluginManager $pluginManager */
        $pluginManager = $this->componizer->resolve(PluginManager::class);

        return $this->findWidgetComponents($pluginManager->getDisabledPlugins());
    }

    /**
     * Finds enabled widget.
     *
     * @param AbstractWidgetComponent|string $widget
     * @return AbstractWidgetComponent|null
     */
    public function findEnabledWidget($widget)
    {
        return $this->findWidgetComponent($widget, $this->getEnabledWidgets());
    }

    /**
     * Tells if widget enabled.
     *
     * @param AbstractWidgetComponent|string $widget
     * @return bool
     */
    public function isWidgetEnabled($widget)
    {
        return $this->findEnabledWidget($widget) !== null;
    }

    //-----------------------------------------------------
    // Allow/Deny section
    //-----------------------------------------------------

    /**
     * Returns allowed widgets.
     *
     * @return AbstractWidgetComponent[]
     */
    public function getAllowedWidgets()
    {
        // todo: implement using SettingsManager in future
        return $this->getEnabledWidgets();
    }

    /**
     * Returns denied widgets.
     *
     * @return AbstractWidgetComponent[]
     */
    public function getDeniedWidgets()
    {
        // todo: implement using SettingsManager in future
        return $this->getDisabledWidgets();
    }

    /**
     * Finds allowed widget.
     *
     * @param AbstractWidgetComponent|string $widget
     * @return AbstractWidgetComponent
     */
    public function findAllowedWidget($widget)
    {
        return $this->findWidgetComponent($widget, $this->getAllowedWidgets());
    }

    /**
     * Tells if widget allowed.
     *
     * @param AbstractWidgetComponent|string $widget
     * @return bool
     */
    public function isWidgetAllowed($widget)
    {
        return $this->findAllowedWidget($widget) !== null;
    }

    //-----------------------------------------------------
    // Pivate methods section
    //-----------------------------------------------------

    /**
     * Finds widgets by plugins.
     *
     * @param AbstractPluginComponent[] $plugins
     * @return AbstractWidgetComponent[]
     */
    private function findWidgetComponents(array $plugins)
    {
        $widgets = [];

        /** @var AbstractPluginComponent $plugin */
        foreach ($plugins as $plugin) {
            /** @var AbstractWidgetComponent $widget */
            foreach ($plugin->getWidgets() as $widget) {
                $widgets[$widget->getId()] = $widget;
            }
        }

        return $widgets;
    }

    /**
     * Finds widget by id or instanve in widgets array.
     *
     * @param AbstractWidgetComponent|string $widget
     * @param AbstractWidgetComponent[] $widgets
     * @return AbstractWidgetComponent|null
     */
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