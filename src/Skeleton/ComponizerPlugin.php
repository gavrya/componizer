<?php
/**
 * Created by PhpStorm.
 * User: gavrya
 * Date: 10/22/15
 * Time: 3:08 PM
 */

namespace Gavrya\Componizer\Skeleton;


abstract class ComponizerPlugin
{

    //-----------------------------------------------------
    // Components section
    //-----------------------------------------------------

    final public function components()
    {
        return array_merge($this->widgets());
    }

    final public function countComponents()
    {
        return count($this->components());
    }

    final public function hasComponents()
    {
        return !empty($this->components());
    }

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

        $components = $components !== null ? $components : $this->components();

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

    final public function hasComponent($component)
    {
        return $this->findComponent($component) !== null;
    }

    //-----------------------------------------------------
    // Widgets section
    //-----------------------------------------------------

    public function widgets()
    {
        return [];
    }

    final public function countWidgets()
    {
        $widgets = $this->widgets();

        return is_array($widgets) ? count($widgets) : 0;
    }

    final public function hasWidgets()
    {
        return $this->countWidgets() > 0;
    }

    final public function hasWidget($widget)
    {
        $widgets = $this->widgets();

        return is_array($widgets) && $this->findComponent($widget, $widgets) instanceof ComponizerWidget;
    }

    //-----------------------------------------------------
    // Other component section
    //-----------------------------------------------------


}