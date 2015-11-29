<?php
/**
 * Created by PhpStorm.
 * User: gavrya
 * Date: 11/24/15
 * Time: 7:39 PM
 */

namespace Gavrya\Componizer\Skeleton;


class ComponizerInternalJs
{

    // include positions
    const POSITION_TOP = 'top';
    const POSITION_BOTTOM = 'bottom';

    // internal vars
    private $script = null;

    //-----------------------------------------------------
    // Create/init section
    //-----------------------------------------------------

    public function __construct($script, $position = self::POSITION_BOTTOM)
    {
        // check script
        if (
            $script === null ||
            !is_string($script) ||
            strtolower(substr(trim($script), 0, strlen('<script'))) !== '<script' ||
            strtolower(substr(trim($script), -strlen('</script>'))) !== '</script>'
        ) {
            throw new InvalidArgumentException('Invalid script');
        }

        $this->script = $script;

        // check js include position
        if (!in_array($position, [self::POSITION_TOP, self::POSITION_BOTTOM])) {
            throw new InvalidArgumentException('Invalid position');
        }

        $this->position = $position;
    }

    //-----------------------------------------------------
    // General methods section
    //-----------------------------------------------------

    public function script()
    {
        return $this->script;
    }

    public function position()
    {
        return $this->position;
    }

    //-----------------------------------------------------
    //  Magic methods section
    //-----------------------------------------------------

    public function __toString()
    {
        return $this->script;
    }

}