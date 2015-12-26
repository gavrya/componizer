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
    // Construct section
    //-----------------------------------------------------

    /**
     * AssetsCollection constructor.
     */
    public function __construct()
    {
        $this->resetAssets($this->addedAssets);
        $this->resetAssets($this->injectedAssets);
    }

    //-----------------------------------------------------
    // General public methods section
    //-----------------------------------------------------

    public function add(array $assets)
    {
        $this->collectAssets($assets, $this->addedAssets);
    }

    public function inject(array $assets)
    {
        $this->collectAssets($assets, $this->injectedAssets);
    }

    public function hasAssets()
    {
        return $this->hasAddedAssets() && $this->hasInjectedAssets();
    }

    public function hasAddedAssets()
    {
        return $this->containsAssets($this->addedAssets);
    }

    public function hasInjectedAssets()
    {
        return $this->containsAssets($this->injectedAssets);
    }

    public function getAssets()
    {
        return array_merge($this->getAddedAssets(), $this->getInjectedAssets());
    }

    public function getAddedAssets()
    {
        return $this->getCollectedAssets($this->addedAssets);
    }

    public function getInjectedAssets()
    {
        return $this->getCollectedAssets($this->injectedAssets);
    }

    public function clearAssets()
    {
        $this->clearAddedAssets();
        $this->clearInjectedAssets();
    }

    public function clearAddedAssets()
    {
        $this->resetAssets($this->addedAssets);
    }

    public function clearInjectedAssets()
    {
        $this->resetAssets($this->injectedAssets);
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

    private function resetAssets(array &$collection)
    {
        $collection = [
            AssetInterface::TYPE_EXTERNAL_JS => [],
            AssetInterface::TYPE_INTERNAL_JS => [],
            AssetInterface::TYPE_EXTERNAL_CSS => [],
            AssetInterface::TYPE_INTERNAL_CSS => [],
        ];
    }

    private function collectAssets(array $assets, array &$collection)
    {
        foreach ($assets as $asset) {
            if ($asset instanceof AssetInterface && array_key_exists($asset->getType(), $collection)) {
                $collection[$asset->getType()][] = $asset;
            }
        }
    }

    private function getCollectedAssets(array &$collection)
    {
        return array_merge(
            $this->$collection[AssetInterface::TYPE_EXTERNAL_JS],
            $this->$collection[AssetInterface::TYPE_INTERNAL_JS],
            $this->$collection[AssetInterface::TYPE_EXTERNAL_CSS],
            $this->$collection[AssetInterface::TYPE_INTERNAL_CSS]
        );
    }

    private function containsAssets(array &$collection)
    {
        return (
            !empty($this->$collection[AssetInterface::TYPE_EXTERNAL_JS]) ||
            !empty($this->$collection[AssetInterface::TYPE_INTERNAL_JS]) ||
            !empty($this->$collection[AssetInterface::TYPE_EXTERNAL_CSS]) ||
            !empty($this->$collection[AssetInterface::TYPE_INTERNAL_CSS])
        );
    }

    private function getAssetsHtml($position, array $options = null)
    {
        $positions = [
            AssetInterface::POSITION_HEAD,
            AssetInterface::POSITION_BODY_TOP,
            AssetInterface::POSITION_BODY_BOTTOM,
        ];

        if (!in_array($position, $positions)) {
            return;
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