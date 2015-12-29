<?php
/**
 * Created by PhpStorm.
 * User: gavrya
 * Date: 11/28/15
 * Time: 1:29 PM
 */

namespace Gavrya\Componizer\Asset;


/**
 * Class AssetsCollection represents a collection of assets.
 *
 * @package Gavrya\Componizer\Asset
 */
class AssetsCollection
{

    // Assets print options
    const OPTION_BASE_URL = 'base_url';

    /**
     * @var AssetInterface[] Added assets
     */
    private $addedAssets = [];

    /**
     * @var AssetInterface[] Injected assets
     */
    private $injectedAssets = [];

    //-----------------------------------------------------
    // General public methods section
    //-----------------------------------------------------

    /**
     * Adds assets.
     *
     * @param array $assets Assets to add
     */
    public function add(array $assets)
    {
        $this->collectAssets($this->addedAssets, $assets);
    }

    /**
     * Injects assets.
     *
     * @param array $assets Assets to inject
     */
    public function inject(array $assets)
    {
        $this->collectAssets($this->injectedAssets, $assets);
    }

    /**
     * Tells if the assets exists.
     *
     * @return bool true if any assets exists, false if not
     */
    public function hasAssets()
    {
        return $this->hasAddedAssets() && $this->hasInjectedAssets();
    }

    /**
     * Tells if the added assets exists.
     *
     * @return bool true if added assets exists, false if not
     */
    public function hasAddedAssets()
    {
        return !empty($this->addedAssets);
    }

    /**
     * Tells if the injected assets exists.
     *
     * @return bool true if injected assets exists, false if not
     */
    public function hasInjectedAssets()
    {
        return !empty($this->injectedAssets);
    }

    /**
     * Returns all assets, added and injected.
     *
     * @return AssetInterface[] Array of assets
     */
    public function getAssets()
    {
        return array_merge($this->getAddedAssets(), $this->getInjectedAssets());
    }

    /**
     * Returns all added assets.
     *
     * @return AssetInterface[] Array of assets
     */
    public function getAddedAssets()
    {
        return $this->addedAssets;
    }

    /**
     * Returns all injected assets.
     *
     * @return AssetInterface[] Array of assets
     */
    public function getInjectedAssets()
    {
        return $this->injectedAssets;
    }

    /**
     * Removes all assets from collection.
     */
    public function clearAssets()
    {
        $this->clearAddedAssets();
        $this->clearInjectedAssets();
    }

    /**
     * Removes all added assets from collection.
     */
    public function clearAddedAssets()
    {
        $this->addedAssets = [];
    }

    /**
     * Removes all injected assets from collection.
     */
    public function clearInjectedAssets()
    {
        $this->injectedAssets = [];
    }

    //-----------------------------------------------------
    // HTML representation methods section
    //-----------------------------------------------------

    /**
     * Returns HTML representation of the assets collection required to be included at HTML head position.
     *
     * @param array|null $options Optional parameters
     * @return string HTML representation of the assets collection
     */
    public function getHeadAssetsHtml(array $options = null)
    {
        return $this->getAssetsHtml(AssetInterface::POSITION_HEAD, $options);
    }

    /**
     * Returns HTML representation of the assets collection required to be included at HTML body top position.
     *
     * @param array|null $options Optional parameters
     * @return string HTML representation of the assets collection
     */
    public function getBodyTopAssetsHtml(array $options = null)
    {
        return $this->getAssetsHtml(AssetInterface::POSITION_BODY_TOP, $options);
    }

    /**
     * Returns HTML representation of the assets collection required to be included at HTML body bottom position.
     *
     * @param array|null $options Optional parameters
     * @return string HTML representation of the assets collection
     */
    public function getBodyBottomAssetsHtml(array $options = null)
    {
        return $this->getAssetsHtml(AssetInterface::POSITION_BODY_BOTTOM, $options);
    }

    //-----------------------------------------------------
    // General private methods section
    //-----------------------------------------------------

    /**
     * Collects newly added or injected assets to the target assets array.
     *
     * @param array $targetAssets Target assets array
     * @param array $assets Assets to add
     */
    private function collectAssets(array &$targetAssets, array $assets)
    {
        $positions = [
            AssetInterface::POSITION_HEAD,
            AssetInterface::POSITION_BODY_TOP,
            AssetInterface::POSITION_BODY_BOTTOM,
        ];

        foreach ($assets as $asset) {
            if ($asset instanceof AssetInterface && in_array($asset->getPosition(), $positions)) {
                $targetAssets[] = $asset;
            }
        }
    }

    /**
     * Returns HTML representation of the assets collection based on include position and other options.
     *
     * @param string $position Include position
     * @param array $options Optional parameters
     * @return string HTML representation of the assets collection
     */
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

        $assetsHtml = '<!-- Assets collection begin -->';

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

        $assetsHtml .= '<!-- Assets collection end -->';

        return $assetsHtml;
    }

    /**
     * Returns option value by the option key.
     *
     * @param array $options Options array
     * @param string $optionKey Option key
     * @return string|null Option value, null otherwise
     */
    private function getOption(array $options, $optionKey)
    {
        return isset($options[$optionKey]) ? $options[$optionKey] : null;
    }

}