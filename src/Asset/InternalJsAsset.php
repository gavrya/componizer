<?php
/**
 * Created by PhpStorm.
 * User: gavrya
 * Date: 11/24/15
 * Time: 7:39 PM
 */

namespace Gavrya\Componizer\Asset;


use InvalidArgumentException;

/**
 * Class InternalJsAsset represents internally included JavaScript.
 *
 * @package Gavrya\Componizer\Asset
 */
class InternalJsAsset implements AssetInterface
{

    /**
     * @var string HTML 'script' element
     */
    private $script = null;

    //-----------------------------------------------------
    // Constructor section
    //-----------------------------------------------------

    public function __construct($script)
    {
        if (
            $script === null ||
            !is_string($script) ||
            strtolower(substr(trim($script), 0, strlen('<script'))) !== '<script' ||
            strtolower(substr(trim($script), -strlen('</script>'))) !== '</script>'
        ) {
            throw new InvalidArgumentException('Invalid script');
        }

        $this->script = $script;
    }

    //-----------------------------------------------------
    // General methods section
    //-----------------------------------------------------

    /**
     * Returns 'script' element HTML.
     *
     * @return string HTML 'script' element
     */
    public function getScript()
    {
        return $this->script;
    }

    //-----------------------------------------------------
    // AssetInterface methods section
    //-----------------------------------------------------

    /**
     * Returns asset include position.
     *
     * @see ComponizerInternalJs::POSITION_* constants
     *
     * @return string One of the position constants value
     */
    public function getPosition()
    {
        return AssetInterface::POSITION_HEAD;
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