<?php
/**
 * Created by PhpStorm.
 * User: gavrya
 * Date: 11/28/15
 * Time: 1:29 PM
 */

namespace Gavrya\Componizer\Skeleton;


class ComponizerAssets
{

    // js assets
    private $externalJs = [];
    private $internalJs = [];

    // css assets
    private $externalCss = [];
    private $internalCss = [];

    //-----------------------------------------------------
    // Create/init section
    //-----------------------------------------------------

    public function __construct(array $assets)
    {
        foreach ($assets as $asset) {
            if ($asset instanceof ComponizerExternalJs) {
                // add external js asset
                $this->externalJs[] = $asset;
            } elseif ($asset instanceof ComponizerInternalJs) {
                // add internal js asset
                $this->internalJs[] = $asset;
            } elseif ($asset instanceof ComponizerExternalCss) {
                // add external css asset
                $this->externalCss[] = $asset;
            } elseif ($asset instanceof ComponizerInternalCss) {
                // add internal css asset
                $this->internalCss[] = $asset;
            }
        }
    }

    //-----------------------------------------------------
    // Display js section
    //-----------------------------------------------------

    public function externalJs()
    {
        return $this->externalJs;
    }

    public function internalJs()
    {
        return $this->internalJs;
    }

    //-----------------------------------------------------
    // Display css section
    //-----------------------------------------------------

    public function externalCss()
    {
        return $this->externalCss;
    }

    public function internalCss()
    {
        return $this->internalCss;
    }

}