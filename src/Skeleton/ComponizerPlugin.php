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

    public function components()
    {
        return array_merge($this->widgets()); // array_merge($this->widgets(), $this->layouts(), ...);
    }

    public function countComponents()
    {
        return count($this->components());
    }

    public function hasComponents()
    {
        return !empty($this->components());
    }

    public function findComponent($component, array $components = null)
    {
        $componentId = null;

        if(is_string($component)){
            $componentId = $component;
        } elseif($component instanceof ComponizerComponent) {
            $componentId = $component->id();
        } else {
            return null;
        }

        $components = $components !== null ? $components : $this->components();

        foreach($components as $item) {
            if($component instanceof ComponizerComponent && $item === $component) {
                return $item;
            }

            if($item instanceof ComponizerComponent && $item->id() === $componentId) {
                return $item;
            }
        }

        return null;
    }

    public function hasComponent($component)
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

    public function countWidgets()
    {
        return count($this->widgets());
    }

    public function hasWidgets()
    {
        return !empty($this->widgets());
    }

    public function hasWidget($widget)
    {
        return $this->findComponent($widget, $this->widgets()) instanceof ComponizerWidget;
    }

    //-----------------------------------------------------
    // Other component section
    //-----------------------------------------------------

}