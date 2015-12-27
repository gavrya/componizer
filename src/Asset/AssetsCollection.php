<?php
/**
 * Created by PhpStorm.
 * User: gavrya
 * Date: 11/28/15
 * Time: 1:29 PM
 */

namespace Gavrya\Componizer\Asset;


/**
 * Incapsulates all nessesary assets.
 *
 * @package Gavrya\Componizer\AssetInterface
 */
class AssetsCollection
{

    // Assets print options
    const OPTION_BASE_URL = 'base_url';

    /**
     * @var array Added assets
     */
    private $addedAssets = [];

    /**
     * @var array Injected assets
     */
    private $injectedAssets = [];

    //-----------------------------------------------------
    // General public methods section
    //-----------------------------------------------------

    public function add(array $assets)
    {
        $this->collectAssets($this->addedAssets, $assets);
    }

    public function inject(array $assets)
    {
        $this->collectAssets($this->injectedAssets, $assets);
    }

    public function hasAssets()
    {
        return $this->hasAddedAssets() && $this->hasInjectedAssets();
    }

    public function hasAddedAssets()
    {
        return !empty($this->addedAssets);
    }

    public function hasInjectedAssets()
    {
        return !empty($this->injectedAssets);
    }

    public function getAssets()
    {
        return array_merge($this->getAddedAssets(), $this->getInjectedAssets());
    }

    public function getAddedAssets()
    {
        return $this->addedAssets;
    }

    public function getInjectedAssets()
    {
        return $this->injectedAssets;
    }

    public function clearAssets()
    {
        $this->clearAddedAssets();
        $this->clearInjectedAssets();
    }

    public function clearAddedAssets()
    {
        $this->addedAssets = [];
    }

    public function clearInjectedAssets()
    {
        $this->injectedAssets = [];
    }

    //-----------------------------------------------------
    // Assets HTML methods section
    //-----------------------------------------------------

    public function getHeadAssetsHtml(array $options = null)
    {
        $this->getAssetsHtml(AssetInterface::POSITION_HEAD, $options);
    }

    public function getBodyTopAssetsHtml(array $options = null)
    {
        $this->getAssetsHtml(AssetInterface::POSITION_BODY_TOP, $options);
    }

    public function getBodyBottomAssetsHtml(array $options = null)
    {
        $this->getAssetsHtml(AssetInterface::POSITION_BODY_BOTTOM, $options);
    }

    //-----------------------------------------------------
    // General private methods section
    //-----------------------------------------------------

    private function collectAssets(array &$collection, array $assets)
    {
        $positions = [
            AssetInterface::POSITION_HEAD,
            AssetInterface::POSITION_BODY_TOP,
            AssetInterface::POSITION_BODY_BOTTOM,
        ];

        foreach ($assets as $asset) {
            if ($asset instanceof AssetInterface && in_array($asset->getPosition(), $positions)) {
                $collection[] = $asset;
            }
        }
    }

    private function getAssetsHtml($position, array $options = [])
    {
        $positions = [
            AssetInterface::POSITION_HEAD,
            AssetInterface::POSITION_BODY_TOP,
            AssetInterface::POSITION_BODY_BOTTOM,
        ];

        if (!in_array($position, $positions)) {
            return '';
        }

        if (!is_array($options)) {
            $options = [];
        }

        $baseUrl = $this->getOption($options, static::OPTION_BASE_URL);

        $assetsHtml = '<!-- Assets section begin -->';

        /** @var AssetInterface $asset */
        foreach ($this->getAssets() as $asset) {
            if ($asset->getPosition() !== $position) {
                continue;
            }

            if ($asset instanceof ExternalCssAsset || $asset instanceof ExternalJsAsset) {
                $assetsHtml .= $asset->toHtml($baseUrl);
            }

            $assetsHtml .= $asset->toHtml();
        }

        $assetsHtml .= '<!-- Assets section end -->';

        return $assetsHtml;
    }

    private function getOption(array $options, $option)
    {
        return isset($options[$option]) ? $options[$option] : null;
    }

}