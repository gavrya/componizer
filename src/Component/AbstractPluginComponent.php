<?php
/**
 * Created by PhpStorm.
 * User: gavrya
 * Date: 10/22/15
 * Time: 3:08 PM
 */

namespace Gavrya\Componizer\Component;

/**
 * Class AbstractPluginComponent required in order to implement plugin.
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
     * @return ComponentInterface[]
     */
    final public function getComponents()
    {
        return array_merge($this->getWidgets());
    }

    /**
     * Returns number of all provided components.
     *
     * @return int
     */
    final public function countComponents()
    {
        return count($this->getComponents());
    }

    /**
     * Checks if the plugin has any components.
     *
     * @return bool
     */
    final public function hasComponents()
    {
        return !empty($this->getComponents());
    }

    /**
     * Checks if the plugin has component.
     *
     * @param string|ComponentInterface $component
     * @return bool
     */
    final public function hasComponent($component)
    {
        return $this->findComponent($component) !== null;
    }

    /**
     * Finds component inside all of its components or inside an array with components.
     *
     * @param string|ComponentInterface $component
     * @param array|null $components
     * @return ComponentInterface|null
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
     * @return AbstractWidgetComponent[]
     */
    public function getWidgets()
    {
        return [];
    }

    /**
     * Returns number of all provided widget components.
     *
     * @return int
     */
    final public function countWidgets()
    {
        return count($this->getWidgets());
    }

    /**
     * Checks if the plugin has any widget components.
     *
     * @return bool
     */
    final public function hasWidgets()
    {
        return !empty($this->getWidgets());
    }

    /**
     * Checks if the plugin has widget component.
     *
     * @param string|AbstractWidgetComponent $widget Widget component id or instance
     * @return bool
     */
    final public function hasWidget($widget)
    {
        return $this->findWidget($widget) !== null;
    }

    /**
     * Finds widget component inside all of its widget components.
     *
     * @param string|AbstractWidgetComponent $widget
     * @return AbstractWidgetComponent|null
     */
    final public function findWidget($widget)
    {
        $component = $this->findComponent($widget, $this->getWidgets());

        return $component instanceof AbstractWidgetComponent ? $component : null;
    }

}