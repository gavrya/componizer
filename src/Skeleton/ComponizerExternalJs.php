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
    const MODE_ASYNC = 'async';
    const MODE_DEFER = 'defer';

    // internal vars
    private $url = null;
    private $position = null;
    private $mode = null;

    //-----------------------------------------------------
    // Create/init section
    //-----------------------------------------------------

    public function __construct($url, $position = self::POSITION_TOP, $mode = '')
    {
        // check js url
        if ($url === null || !is_string($url) || strtolower(substr($url, -strlen('.js'))) !== '.js') {
            throw new InvalidArgumentException('Invalid url');
        }

        $this->url = $url;

        // check js include position
        if (!in_array($position, [self::POSITION_TOP, self::POSITION_BOTTOM])) {
            throw new InvalidArgumentException('Invalid position');
        }

        $this->position = $position;

        // check js execution mode
        if ($mode !== '' && !in_array($mode, [self::MODE_ASYNC, self::MODE_DEFER])) {
            throw new InvalidArgumentException('Invalid mode');
        }

        $this->mode = $mode;
    }

    //-----------------------------------------------------
    // General methods section
    //-----------------------------------------------------

    public function url()
    {
        return $this->url;
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
        if ($this->mode === '') {
            return '<script src="' . $this->url . '"></script>';
        } else {
            return '<script src="' . $this->url . '" ' . $this->mode . '></script>';
        }
    }
}