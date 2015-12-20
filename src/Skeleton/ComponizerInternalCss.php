<?php
/**
 * Created by PhpStorm.
 * User: gavrya
 * Date: 11/24/15
 * Time: 7:33 PM
 */

namespace Gavrya\Componizer\Skeleton;


use InvalidArgumentException;

/**
 * Represents internally included CSS.
 *
 * @package Gavrya\Componizer\Skeleton
 */
class ComponizerInternalCss
{

    /**
     * @var string HTML 'style' element
     */
    private $style = null;

    //-----------------------------------------------------
    // Instance create/init section
    //-----------------------------------------------------

    /**
     * ComponizerInternalCss constructor.
     *
     * @param string $style HTML 'style' element
     */
    public function __construct($style)
    {
        if (
            $style === null ||
            !is_string($style) ||
            strtolower(substr(trim($style), 0, strlen('<style>'))) !== '<style>' ||
            strtolower(substr(trim($style), -strlen('</style>'))) !== '</style>'
        ) {
            throw new InvalidArgumentException('Invalid style');
        }

        $this->style = $style;
    }

    //-----------------------------------------------------
    // General methods section
    //-----------------------------------------------------

    /**
     * Returns 'style' element HTML.
     *
     * @return string HTML 'style' element
     */
    public function style()
    {
        return $this->style;
    }

    /**
     * Returns HTML representation of the asset.
     *
     * @return string HTML 'style' element
     */
    public function toHtml()
    {
        return $this->style;
    }

}