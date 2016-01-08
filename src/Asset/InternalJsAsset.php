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
     * @var string
     */
    private $hash = null;

    /**
     * @var string
     */
    private $position = null;

    /**
     * @var string
     */
    private $script = null;

    //-----------------------------------------------------
    // Constructor section
    //-----------------------------------------------------

    public function __construct($script, $position = AssetInterface::POSITION_HEAD)
    {
        if (
            $script === null ||
            !is_string($script) ||
            strtolower(substr(trim($script), 0, strlen('<script'))) !== '<script' ||
            strtolower(substr(trim($script), -strlen('</script>'))) !== '</script>'
        ) {
            throw new InvalidArgumentException('Invalid script');
        }

        $positions = [
            AssetInterface::POSITION_HEAD,
            AssetInterface::POSITION_BODY_TOP,
            AssetInterface::POSITION_BODY_BOTTOM,
        ];

        if (!in_array($position, $positions)) {
            throw new InvalidArgumentException(sprintf('Invalid position: %s', $position));
        }

        $this->hash = md5($script);
        $this->position = $position;
        $this->script = $script;
    }

    //-----------------------------------------------------
    // General methods section
    //-----------------------------------------------------

    /**
     * Returns 'script' element HTML.
     *
     * @return string
     */
    public function getScript()
    {
        return $this->script;
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
     * @see ComponizerInternalJs::POSITION_* constants
     *
     * @return string
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * Returns HTML representation of the asset.
     *
     * @return string
     */
    public function toHtml()
    {
        return $this->script;
    }

}