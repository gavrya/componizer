<?php
/**
 * Created by PhpStorm.
 * User: gavrya
 * Date: 11/24/15
 * Time: 7:39 PM
 */

namespace Gavrya\Componizer\Skeleton;


use InvalidArgumentException;

class ComponizerExternalJs
{

    // include positions
    const POSITION_TOP = 'top';
    const POSITION_BOTTOM = 'bottom';

    // execution modes
    const MODE_DEFAULT = 'default';
    const MODE_ASYNC = 'async';
    const MODE_DEFER = 'defer';

    // internal vars
    private $path = null;
    private $position = null;
    private $mode = null;

    //-----------------------------------------------------
    // Create/init section
    //-----------------------------------------------------

    public function __construct($path, $position = self::POSITION_BOTTOM, $mode = self::MODE_DEFAULT)
    {
        // check js src path
        if ($path === null || !is_string($path) || strtolower(substr($path, -strlen('.js'))) !== '.js') {
            throw new InvalidArgumentException('Invalid path');
        }

        $this->path = $path;

        // check js include position
        if (!in_array($position, [self::POSITION_TOP, self::POSITION_BOTTOM])) {
            throw new InvalidArgumentException('Invalid position');
        }

        $this->position = $position;

        // check js execution mode
        if (!in_array($mode, [self::MODE_DEFAULT, self::MODE_ASYNC, self::MODE_DEFER])) {
            throw new InvalidArgumentException('Invalid mode');
        }

        $this->mode = $mode;
    }

    //-----------------------------------------------------
    // General methods section
    //-----------------------------------------------------

    public function path()
    {
        return $this->path;
    }

    public function position()
    {
        return $this->position;
    }

    public function mode()
    {
        return $this->mode;
    }

    //-----------------------------------------------------
    //  Magic methods section
    //-----------------------------------------------------

    public function __toString()
    {
        if ($this->mode === self::MODE_DEFAULT) {
            return '<script type="text/javascript" src="' . $this->path . '"></script>';
        } else {
            return '<script type="text/javascript" src="' . $this->path . '" ' . $this->mode . '></script>';
        }
    }
}