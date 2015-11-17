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

    final public function hasComponent($component)
    {
        return $this->findComponent($component) !== null;
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

    //-----------------------------------------------------
    // Widgets section
    //-----------------------------------------------------

    public function widgets()
    {
        return [];
    }

    final public function countWidgets()
    {
        return count($this->widgets());
    }

    final public function hasWidgets()
    {
        return !empty($this->widgets());
    }

    final public function hasWidget($widget)
    {
        return $this->getWidget($widget) !== null;
    }

    final public function getWidget($widget)
    {
        $widget = $this->findComponent($widget, $this->widgets());

        return $widget instanceof ComponizerWidget ? $widget : null;
    }

    //-----------------------------------------------------
    // Other component section
    //-----------------------------------------------------


}