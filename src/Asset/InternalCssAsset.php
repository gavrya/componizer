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
 * Class InternalCssAsset represents internally included CSS.
 *
 * @package Gavrya\Componizer\Asset
 */
class InternalCssAsset implements AssetInterface
{

    /**
     * @var string
     */
    private $hash = null;

    /**
     * @var string
     */
    private $style = null;

    //-----------------------------------------------------
    // Constructor section
    //-----------------------------------------------------

    /**
     * InternalCssAsset constructor.
     *
     * @param string $style
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

        $this->hash = md5($style);
        $this->style = $style;
    }

    //-----------------------------------------------------
    // General methods section
    //-----------------------------------------------------

    /**
     * Returns 'style' element HTML.
     *
     * @return string
     */
    public function getStyle()
    {
        return $this->style;
    }

    //-----------------------------------------------------
    // AssetInterface methods section
    //-----------------------------------------------------

    /**
     * Returns asset unique hash.
     *
     * @return string
     */
    public function getHash()
    {
        return $this->hash;
    }

    /**
     * Returns asset include position.
     *
     * @see ComponizerAsset::POSITION_* constants
     *
     * @return string
     */
    public function getPosition()
    {
        return AssetInterface::POSITION_HEAD;
    }

    /**
     * Returns HTML representation of the asset.
     *
     * @param array|null $options
     * @return string
     */
    public function toHtml(array $options = null)
    {
        return $this->style;
    }

}