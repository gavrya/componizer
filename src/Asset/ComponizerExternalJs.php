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
 * Represents externally included JavaScript.
 *
 * @package Gavrya\Componizer\Asset
 */
class ComponizerExternalJs implements ComponizerAsset
{

    // JavaScript execution modes
    const MODE_ASYNC = 'async';
    const MODE_DEFER = 'defer';

    /**
     * @var string Relative or absolute link to the JavaScript file.
     */
    private $url = null;

    /**
     * @var string Asset include position
     */
    private $position = null;

    /**
     * @var string|null JavaScript execution mode
     */
    private $mode = null;

    //-----------------------------------------------------
    // Construct section
    //-----------------------------------------------------

    /**
     * ComponizerExternalJs constructor.
     *
     * @param string $url Relative or absolute link to the JavaScript file
     * @param string $position Asset required include position
     * @param string $mode Required JavaScript execution mode
     */
    public function __construct($url, $position = ComponizerAsset::POSITION_HEAD, $mode = null)
    {
        if ($url === null || !is_string($url) || strtolower(substr($url, -strlen('.js'))) !== '.js') {
            throw new InvalidArgumentException(sprintf('Invalid url: %s', $url));
        }

        $positions = [
            ComponizerAsset::POSITION_HEAD,
            ComponizerAsset::POSITION_BODY_TOP,
            ComponizerAsset::POSITION_BODY_BOTTOM,
        ];

        if (!in_array($position, $positions)) {
            throw new InvalidArgumentException(sprintf('Invalid position: %s', $position));
        }

        if ($mode !== null && !in_array($mode, [static::MODE_ASYNC, static::MODE_DEFER])) {
            throw new InvalidArgumentException(sprintf('Invalid mode: %s', $mode));
        }

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
     * @return string Link to the JavaScript file
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
        return ComponizerAsset::TYPE_EXTERNAL_JS;
    }

    /**
     * Returns asset include position.
     *
     * @see ComponizerExternalJs::POSITION_* constants
     *
     * @return string One of the position constants value
     */
    final public function getPosition()
    {
        return $this->position;
    }

    /**
     * Returns HTML representation of the asset.
     *
     * @param string|null $baseUrl Optional base url to prefix existed relative url
     * @return string HTML 'script' element
     */
    public function toHtml($baseUrl = null)
    {
        $targetUrl = $this->url;

        if (is_string($baseUrl) && strpos($this->url, '/') === 0 && strpos($this->url, '//') !== 0) {
            $targetUrl = rtrim($baseUrl, '/') . $this->url;
        }

        if ($this->mode === null) {
            return sprintf('<script src="%s" type="text/javascript"></script>', $targetUrl);
        }

        return sprintf('<script src="%s" type="text/javascript" %s></script>', $targetUrl, $this->mode);
    }
}