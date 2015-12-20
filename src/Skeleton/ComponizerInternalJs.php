<?php
/**
 * Created by PhpStorm.
 * User: gavrya
 * Date: 11/24/15
 * Time: 7:39 PM
 */

namespace Gavrya\Componizer\Skeleton;


use InvalidArgumentException;

/**
 * Represents internally included JavaScript.
 *
 * @package Gavrya\Componizer\Skeleton
 */
class ComponizerInternalJs
{

    // include positions
    const POSITION_TOP = 'top';
    const POSITION_BOTTOM = 'bottom';

    /**
     * @var string HTML 'script' element
     */
    private $script = null;

    //-----------------------------------------------------
    // Instance create/init section
    //-----------------------------------------------------

    public function __construct($script, $position = self::POSITION_BOTTOM)
    {
        if (
            $script === null ||
            !is_string($script) ||
            strtolower(substr(trim($script), 0, strlen('<script>'))) !== '<script>' ||
            strtolower(substr(trim($script), -strlen('</script>'))) !== '</script>'
        ) {
            throw new InvalidArgumentException('Invalid script');
        }

        if (!in_array($position, [self::POSITION_TOP, self::POSITION_BOTTOM])) {
            throw new InvalidArgumentException(sprintf('Invalid position: %s', $position));
        }

        $this->script = $script;
        $this->position = $position;
    }

    //-----------------------------------------------------
    // General methods section
    //-----------------------------------------------------

    /**
     * Returns 'script' element HTML.
     *
     * @return string HTML 'script' element
     */
    public function script()
    {
        return $this->script;
    }

    /**
     * Returns asset include position.
     *
     * @see ComponizerInternalJs::POSITION_* constants
     *
     * @return string One of the position constants value
     */
    public function position()
    {
        return $this->position;
    }

    /**
     * Returns HTML representation of the asset.
     *
     * @return string HTML 'script' element
     */
    public function toHtml()
    {
        return $this->script;
    }

}