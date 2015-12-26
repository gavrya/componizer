<?php
/**
 * Created by PhpStorm.
 * User: gavrya
 * Date: 11/24/15
 * Time: 7:33 PM
 */

namespace Gavrya\Componizer\Asset;


use InvalidArgumentException;

/**
 * Represents internally included CSS.
 *
 * @package Gavrya\Componizer\AssetInterface
 */
class InternalCssAsset implements AssetInterface
{

    /**
     * @var string HTML 'style' element
     */
    private $style = null;

    //-----------------------------------------------------
    // Construct section
    //-----------------------------------------------------

    /**
     * InternalCssAsset constructor.
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
    public function getStyle()
    {
        return $this->style;
    }

    //-----------------------------------------------------
    // AssetInterface methods section
    //-----------------------------------------------------

    /**
     * Returns type of the asset.
     *
     * @see ComponizerAsset::TYPE_* constants
     *
     * @return string AssetInterface type
     */
    final public function getType()
    {
        return AssetInterface::TYPE_INTERNAL_CSS;
    }

    /**
     * Returns asset include position.
     *
     * @see ComponizerAsset::POSITION_* constants
     *
     * @return string Include position value
     */
    final public function getPosition()
    {
        return AssetInterface::POSITION_HEAD;
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