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

    public function hasComponent($component)
    {
        $componentId = null;

        if(is_string($component) && is_numeric($component)){
            $componentId = (string) $component;
        } elseif($component instanceof ComponizerComponent) {
            $componentId = $component->id();
        } else {
            return false;
        }

        foreach($this->components() as $item) {
            if($item instanceof ComponizerComponent && $item->id() === $componentId) {
                return true;
            }
        }

        return false;
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
        return $this->hasComponent($widget);
    }

    //-----------------------------------------------------
    // Other component section
    //-----------------------------------------------------

}