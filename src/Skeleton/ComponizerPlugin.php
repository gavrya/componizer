<?php
/**
 * Created by PhpStorm.
 * User: gavrya
 * Date: 10/22/15
 * Time: 3:08 PM
 */

namespace Gavrya\Componizer\Skeleton;

/**
 * Required in order to implement componizer plugin.
 *
 * @package Gavrya\Componizer\Skeleton
 */
abstract class ComponizerPlugin
{

    //-----------------------------------------------------
    // Components related methods section
    //-----------------------------------------------------

    /**
     * Returns an array with all provided components.
     *
     * @return ComponizerComponent[] An array with components
     */
    final public function components()
    {
        return array_merge($this->widgets());
    }

    /**
     * Returns number of all provided components.
     *
     * @return int Number of components
     */
    final public function countComponents()
    {
        return count($this->components());
    }

    /**
     * Checks if the plugin has any components.
     *
     * @return bool true if plugin has at least one component, false otherwise
     */
    final public function hasComponents()
    {
        return !empty($this->components());
    }

    /**
     * Checks if the plugin has component.
     *
     * @param string|ComponizerComponent $component Component id or instance
     * @return bool true if it has, false otherwise
     */
    final public function hasComponent($component)
    {
        return $this->findComponent($component) !== null;
    }

    /**
     * Finds component inside all of its components or inside an array with components.
     *
     * @param string|ComponizerComponent $component Component id or instance
     * @param array|null $components Optional array with components to search in
     * @return ComponizerComponent|null Found component, null otherwise
     */
    final public function findComponent($component, array $components = null)
    {
        $componentId = null;

        if (is_string($component)) {
            $componentId = $component;
        } elseif ($component instanceof ComponizerComponent) {
            $componentId = $component->id();
        } else {
            return null;
        }

        if ($components === null) {
            $components = $this->components();
        }

        foreach ($components as $item) {
            if ($component instanceof ComponizerComponent && $item === $component) {
                return $item;
            }

            if ($item instanceof ComponizerComponent && $item->id() === $componentId) {
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
     * @return ComponizerWidget[] An array with widget components
     */
    public function widgets()
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
        return count($this->widgets());
    }

    /**
     * Checks if the plugin has any widget components.
     *
     * @return bool true if plugin has at least one widget component, false otherwise
     */
    final public function hasWidgets()
    {
        return !empty($this->widgets());
    }

    /**
     * Checks if the plugin has widget component.
     *
     * @param string|ComponizerWidget $widget Widget component id or instance
     * @return bool true if it has, false otherwise
     */
    final public function hasWidget($widget)
    {
        return $this->findWidget($widget) !== null;
    }

    /**
     * Finds widget component inside all of its widget components.
     *
     * @param string|ComponizerWidget $widget Component id or instance
     * @return ComponizerWidget|null Found widget component, null otherwise
     */
    final public function findWidget($widget)
    {
        $component = $this->findComponent($widget, $this->widgets());

        return $component instanceof ComponizerWidget ? $component : null;
    }

}