<?php
/**
 * Created by PhpStorm.
 * User: gavrya
 * Date: 11/24/15
 * Time: 7:32 PM
 */

namespace Gavrya\Componizer\Skeleton;


class ComponizerExternalCss
{

    // internal vars
    private $url = null;
    private $media = null;

    //-----------------------------------------------------
    // Create/init section
    //-----------------------------------------------------

    public function __construct($url, $media = null)
    {
        // check css url
        if ($url === null || !is_string($url) || strtolower(substr($url, -strlen('.css'))) !== '.css') {
            throw new InvalidArgumentException('Invalid url');
        }

        $this->url = $url;

        // check css media
        if ($media !== null || !is_string($media)) {
            throw new InvalidArgumentException('Invalid media');
        }

        $this->media = $media;
    }

    //-----------------------------------------------------
    // General methods section
    //-----------------------------------------------------

    public function url()
    {
        return $this->url;
    }

    public function media()
    {
        return $this->media;
    }

    //-----------------------------------------------------
    //  Magic methods section
    //-----------------------------------------------------

    public function __toString()
    {
        if ($this->media === null) {
            return '<link href="' . $this->url . '" rel="stylesheet">';
        } else {
            return '<link href="' . $this->url . '" rel="stylesheet" media="' . $this->media . '">';
        }
    }

}