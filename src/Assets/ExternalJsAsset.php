<?php
/**
 * Created by PhpStorm.
 * User: gavrya
 * Date: 11/24/15
 * Time: 7:39 PM
 */

namespace Gavrya\Componizer\Assets;


use InvalidArgumentException;

/**
 * Class ExternalJsAsset represents externally included JavaScript.
 *
 * @package Gavrya\Componizer\Assets
 */
class ExternalJsAsset implements AssetInterface
{

    // JavaScript execution modes
    const MODE_ASYNC = 'async';
    const MODE_DEFER = 'defer';

    /**
     * @var string
     */
    private $hash = null;

    /**
     * @var string
     */
    private $url = null;

    /**
     * @var string
     */
    private $position = null;

    /**
     * @var string|null
     */
    private $mode = null;

    //-----------------------------------------------------
    // Constructor section
    //-----------------------------------------------------

    /**
     * ExternalJsAsset constructor.
     *
     * @param string $url
     * @param string $position
     * @param string $mode
     */
    public function __construct($url, $position = AssetInterface::POSITION_HEAD, $mode = null)
    {
        if ($url === null || !is_string($url) || strtolower(substr($url, -strlen('.js'))) !== '.js') {
            throw new InvalidArgumentException(sprintf('Invalid url: %s', $url));
        }

        $positions = [
            AssetInterface::POSITION_HEAD,
            AssetInterface::POSITION_BODY_TOP,
            AssetInterface::POSITION_BODY_BOTTOM,
        ];

        if (!in_array($position, $positions)) {
            throw new InvalidArgumentException(sprintf('Invalid position: %s', $position));
        }

        if ($mode !== null && !in_array($mode, [static::MODE_ASYNC, static::MODE_DEFER])) {
            throw new InvalidArgumentException(sprintf('Invalid mode: %s', $mode));
        }

        $this->hash = md5($url);
        $this->url = $url;
        $this->position = $position;
        $this->mode = $mode;
    }

    //-----------------------------------------------------
    // General methods section
    //-----------------------------------------------------

    /**
     * Returns link to the JavaScript file.
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Returns JavaScript execution mode.
     *
     * @param mixed $default
     * @return string|null
     */
    public function getMode($default = null)
    {
        return isset($this->mode) ? $this->mode : $default;
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
     * @see ComponizerExternalJs::POSITION_* constants
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
        if ($this->mode === null) {
            return sprintf('<script src="%s"></script>', $this->url);
        }

        return sprintf('<script src="%s" %s></script>', $this->url, $this->mode);
    }
}