<?php
/**
 * Created by PhpStorm.
 * User: gavrya
 * Date: 11/24/15
 * Time: 7:32 PM
 */

namespace Gavrya\Componizer\Asset;


use InvalidArgumentException;

/**
 * Class ExternalCssAsset represents externally included CSS.
 *
 * @package Gavrya\Componizer\Asset
 */
class ExternalCssAsset implements AssetInterface
{

    /**
     * @var string
     */
    private $hash = null;

    /**
     * @var string
     */
    private $url = null;

    /**
     * @var string|null
     */
    private $media = null;

    //-----------------------------------------------------
    // Constructor section
    //-----------------------------------------------------

    /**
     * ExternalCssAsset constructor.
     *
     * @param string $url
     * @param string $media
     * @throws InvalidArgumentException
     */
    public function __construct($url, $media = null)
    {
        if ($url === null || !is_string($url) || strtolower(substr($url, -strlen('.css'))) !== '.css') {
            throw new InvalidArgumentException(sprintf('Invalid url: %s', $url));
        }

        if ($media !== null && !is_string($media)) {
            throw new InvalidArgumentException(sprintf('Invalid media: %s', $media));
        }

        $this->hash = md5($url);
        $this->url = $url;
        $this->media = $media;
    }

    //-----------------------------------------------------
    // General methods section
    //-----------------------------------------------------

    /**
     * Returns link to the CSS file.
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Returns CSS link media attribute.
     *
     * @param mixed $default
     * @return string|null
     */
    public function getMedia($default = null)
    {
        return isset($this->media) ? $this->media : $default;
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
     * @return string
     */
    public function toHtml()
    {
        if ($this->media === null) {
            return sprintf('<link href="%s" rel="stylesheet">', $this->url);
        }

        return sprintf('<link href="%s" rel="stylesheet" media="%s">', $this->url, $this->media);
    }

}