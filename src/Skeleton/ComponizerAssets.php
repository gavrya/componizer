<?php
/**
 * Created by PhpStorm.
 * User: gavrya
 * Date: 11/28/15
 * Time: 1:29 PM
 */

namespace Gavrya\Componizer\Skeleton;


/**
 * Incapsulates all nessesary assets.
 *
 * @package Gavrya\Componizer\Skeleton
 */
class ComponizerAssets
{
    /**
     * @var ComponizerExternalJs[] External JavaScript assets
     */
    private $externalJs = [];

    /**
     * @var ComponizerInternalJs[] Internal JavaScript assets
     */
    private $internalJs = [];

    /**
     * @var ComponizerExternalCss[] External CSS assets
     */
    private $externalCss = [];

    /**
     * @var ComponizerInternalCss[] Internal CSS assets
     */
    private $internalCss = [];

    //-----------------------------------------------------
    // Construct section
    //-----------------------------------------------------

    /**
     * ComponizerAssets constructor.
     *
     * @param array $assets Array with componizer assets
     */
    public function __construct(array $assets = [])
    {
        foreach ($assets as $asset) {
            if ($asset instanceof ComponizerExternalJs) {
                $this->externalJs[] = $asset;
            } elseif ($asset instanceof ComponizerInternalJs) {
                $this->internalJs[] = $asset;
            } elseif ($asset instanceof ComponizerExternalCss) {
                $this->externalCss[] = $asset;
            } elseif ($asset instanceof ComponizerInternalCss) {
                $this->internalCss[] = $asset;
            }
        }
    }

    //-----------------------------------------------------
    // JavaScript assets methods section
    //-----------------------------------------------------

    /**
     * Returns array of external JavaScript assets.
     *
     * @return ComponizerExternalJs[] Array of assets
     */
    public function externalJs()
    {
        return $this->externalJs;
    }

    /**
     * Returns array of internal JavaScript assets.
     *
     * @return ComponizerInternalJs[] Array of assets
     */
    public function internalJs()
    {
        return $this->internalJs;
    }

    //-----------------------------------------------------
    // CSS assets methods section
    //-----------------------------------------------------

    /**
     * Returns array of external CSS assets.
     *
     * @return ComponizerExternalCss[] Array of assets
     */
    public function externalCss()
    {
        return $this->externalCss;
    }

    /**
     * Returns array of internal CSS assets.
     *
     * @return ComponizerInternalCss[] Array of assets
     */
    public function internalCss()
    {
        return $this->internalCss;
    }

}