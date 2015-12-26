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
 * Represents externally included CSS.
 *
 * @package Gavrya\Componizer\Asset
 */
class ComponizerExternalCss implements ComponizerAsset
{

    /**
     * @var string Relative or absolute link to the CSS file.
     */
    private $url = null;

    /**
     * @var string HTML link element media attributes.
     */
    private $media = null;

    //-----------------------------------------------------
    // Construct section
    //-----------------------------------------------------

    /**
     * ComponizerExternalCss constructor.
     *
     * @param string $url Relative or absolute link to the CSS file
     * @param string $media HTML link element media attributes
     * @throws InvalidArgumentException When arguments was invalid
     */
    public function __construct($url, $media = null)
    {
        if ($url === null || !is_string($url) || strtolower(substr($url, -strlen('.css'))) !== '.css') {
            throw new InvalidArgumentException(sprintf('Invalid url: %s', $url));
        }

        if ($media !== null && !is_string($media)) {
            throw new InvalidArgumentException(sprintf('Invalid media: %s', $media));
        }

        $this->url = $url;
        $this->media = $media;
    }

    //-----------------------------------------------------
    // General methods section
    //-----------------------------------------------------

    /**
     * Returns link to the CSS file.
     *
     * @return string Link to the CSS file
     */
    public function url()
    {
        return $this->url;
    }

    //-----------------------------------------------------
    // ComponizerAsset methods section
    //-----------------------------------------------------

    /**
     * Returns type of the asset.
     *
     * @see ComponizerAsset::TYPE_* constants
     *
     * @return string Asset type
     */
    final public function getType()
    {
        return ComponizerAsset::TYPE_EXTERNAL_CSS;
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
        return ComponizerAsset::POSITION_HEAD;
    }

    /**
     * Returns HTML representation of the asset.
     *
     * @param string|null $baseUrl Optional base url to prefix an existed relative url
     * @return string HTML 'link' element
     */
    public function toHtml($baseUrl = null)
    {
        $targetUrl = $this->url;

        if (is_string($baseUrl) && strpos($this->url, '/') === 0 && strpos($this->url, '//') !== 0) {
            $targetUrl = rtrim($baseUrl, '/') . $this->url;
        }

        if ($this->media === null) {
            return sprintf('<link href="%s" rel="stylesheet">', $targetUrl);
        }

        return sprintf('<link href="%s" rel="stylesheet" media="%s">', $targetUrl, $this->media);
    }

}