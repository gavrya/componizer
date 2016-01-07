<?php
/**
 * Created by PhpStorm.
 * User: gavrya
 * Date: 11/28/15
 * Time: 1:29 PM
 */

namespace Gavrya\Componizer\Asset;


/**
 * Class AssetsCollection represents a collection of added and injected assets.
 *
 * @package Gavrya\Componizer\Asset
 */
class AssetsCollection
{

    /**
     * @var AssetInterface[]
     */
    private $addedAssets = [];

    /**
     * @var AssetInterface[]
     */
    private $injectedAssets = [];

    //-----------------------------------------------------
    // General public methods section
    //-----------------------------------------------------

    /**
     * Adds assets.
     *
     * @param array $assets
     */
    public function add(array $assets)
    {
        $this->collectAssets($this->addedAssets, $assets);
    }

    /**
     * Injects assets.
     *
     * @param array $assets
     */
    public function inject(array $assets)
    {
        $this->collectAssets($this->injectedAssets, $assets);
    }

    /**
     * Tells if the assets exists.
     *
     * @return bool
     */
    public function hasAssets()
    {
        return $this->hasAddedAssets() || $this->hasInjectedAssets();
    }

    /**
     * Tells if the added assets exists.
     *
     * @return bool
     */
    public function hasAddedAssets()
    {
        return !empty($this->addedAssets);
    }

    /**
     * Tells if the injected assets exists.
     *
     * @return bool
     */
    public function hasInjectedAssets()
    {
        return !empty($this->injectedAssets);
    }

    /**
     * Returns all assets with unique hashes.
     *
     * @return AssetInterface[]
     */
    public function getAssets()
    {
        return array_merge($this->getAddedAssets(), $this->getInjectedAssets());
    }

    /**
     * Returns all added assets with unique hashes.
     *
     * @return AssetInterface[]
     */
    public function getAddedAssets()
    {
        return $this->addedAssets;
    }

    /**
     * Returns all injected assets with unique hashes.
     *
     * @return AssetInterface[]
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
    // Assets HTML methods section
    //-----------------------------------------------------

    /**
     * Returns HTML representation of the assets collection required to be included at HTML head position.
     *
     * @return string
     */
    public function getHeadAssetsHtml()
    {
        return $this->getAssetsHtml(AssetInterface::POSITION_HEAD);
    }

    /**
     * Returns HTML representation of the assets collection required to be included at HTML body top position.
     *
     * @return string
     */
    public function getBodyTopAssetsHtml()
    {
        return $this->getAssetsHtml(AssetInterface::POSITION_BODY_TOP);
    }

    /**
     * Returns HTML representation of the assets collection required to be included at HTML body bottom position.
     *
     * @return string
     */
    public function getBodyBottomAssetsHtml()
    {
        return $this->getAssetsHtml(AssetInterface::POSITION_BODY_BOTTOM);
    }

    //-----------------------------------------------------
    // General private methods section
    //-----------------------------------------------------

    /**
     * Collects newly added or injected assets to the target assets array.
     *
     * @param array $targetAssets
     * @param array $assets
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
                $targetAssets[$asset->getHash()] = $asset;
            }
        }
    }

    /**
     * Returns HTML representation of the assets collection based on include position and other options.
     *
     * @param string $position
     * @return string
     */
    private function getAssetsHtml($position)
    {
        $positions = [
            AssetInterface::POSITION_HEAD,
            AssetInterface::POSITION_BODY_TOP,
            AssetInterface::POSITION_BODY_BOTTOM,
        ];

        if (!in_array($position, $positions)) {
            return '';
        }

        $assetsHtml = '<!-- Assets collection begin -->';

        /** @var AssetInterface $asset */
        foreach ($this->getAssets() as $asset) {
            if ($asset->getPosition() !== $position) {
                continue;
            }

            $assetsHtml .= $asset->toHtml();
        }

        $assetsHtml .= '<!-- Assets collection end -->';

        return $assetsHtml;
    }

}