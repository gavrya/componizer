<?php
/**
 * Created by PhpStorm.
 * User: gavrya
 * Date: 10/22/15
 * Time: 3:08 PM
 */

namespace Gavrya\Componizer\Component;

/**
 * Required in order to implement componizer plugin.
 *
 * @package Gavrya\Componizer\Component
 */
abstract class AbstractPluginComponent implements ComponentInterface
{

    //-----------------------------------------------------
    // Components related methods section
    //-----------------------------------------------------

    /**
     * Returns an array with all provided components.
     *
     * @return ComponentInterface[] An array with components
     */
    final public function getComponents()
    {
        return array_merge($this->getWidgets());
    }

    /**
     * Returns number of all provided components.
     *
     * @return int Number of components
     */
    final public function countComponents()
    {
        return count($this->getComponents());
    }

    /**
     * Checks if the plugin has any components.
     *
     * @return bool true if plugin has at least one component, false otherwise
     */
    final public function hasComponents()
    {
        return !empty($this->getComponents());
    }

    /**
     * Checks if the plugin has component.
     *
     * @param string|ComponentInterface $component Component id or instance
     * @return bool true if it has, false otherwise
     */
    final public function hasComponent($component)
    {
        return $this->findComponent($component) !== null;
    }

    /**
     * Finds component inside all of its components or inside an array with components.
     *
     * @param string|ComponentInterface $component Component id or instance
     * @param array|null $components Optional array with components to search in
     * @return ComponentInterface|null Found component, null otherwise
     */
    final public function findComponent($component, array $components = null)
    {
        $componentId = null;

        if (is_string($component)) {
            $componentId = $component;
        } elseif ($component instanceof ComponentInterface) {
            $componentId = $component->getId();
        } else {
            return null;
        }

        if ($components === null) {
            $components = $this->getComponents();
        }

        foreach ($components as $item) {
            if ($component instanceof ComponentInterface && $item === $component) {
                return $item;
            }

            if ($item instanceof ComponentInterface && $item->getId() === $componentId) {
                return $item;
            }
        }

        return null;
    }

    //-----------------------------------------------------
    // Widgets related methods section
    //-----------------------------------------------------

    /**
     * Returns an array with all provided widget components.
     *
     * This method should be overridden in widget implementation classes.
     *
     * @return AbstractWidgetComponent[] An array with widget components
     */
    public function getWidgets()
    {
        return [];
    }

    /**
     * Returns number of all provided widget components.
     *
     * @return int Number of widget components
     */
    final public function countWidgets()
    {
        return count($this->getWidgets());
    }

    /**
     * Checks if the plugin has any widget components.
     *
     * @return bool true if plugin has at least one widget component, false otherwise
     */
    final public function hasWidgets()
    {
        return !empty($this->getWidgets());
    }

    /**
     * Checks if the plugin has widget component.
     *
     * @param string|AbstractWidgetComponent $widget Widget component id or instance
     * @return bool true if it has, false otherwise
     */
    final public function hasWidget($widget)
    {
        return $this->findWidget($widget) !== null;
    }

    /**
     * Finds widget component inside all of its widget components.
     *
     * @param string|AbstractWidgetComponent $widget Component id or instance
     * @return AbstractWidgetComponent|null Found widget component, null otherwise
     */
    final public function findWidget($widget)
    {
        $component = $this->findComponent($widget, $this->getWidgets());

        return $component instanceof AbstractWidgetComponent ? $component : null;
    }

}