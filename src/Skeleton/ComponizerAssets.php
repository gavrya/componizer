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

    // Assets print options
    const OPTION_BASE_URL = 'base_url';

    /**
     * @var ComponizerExternalJs[] External JavaScript assets
     */
    private $addedAssets = [];

    /**
     * @var ComponizerExternalJs[] External JavaScript assets
     */
    private $injectedAssets = [];

    //-----------------------------------------------------
    // Construct section
    //-----------------------------------------------------

    /**
     * ComponizerAssets constructor.
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

    public function printAssets($position, array $options = null)
    {
        $positions = [
            ComponizerAsset::POSITION_HEAD,
            ComponizerAsset::POSITION_BODY_TOP,
            ComponizerAsset::POSITION_BODY_BOTTOM,
        ];

        if (!in_array($position, $positions)) {
            return;
        }

        $baseUrl = $this->getOption(static::OPTION_BASE_URL);

        /** @var ComponizerAsset $asset */
        foreach ($this->getAssets() as $asset) {
            if ($asset->getPosition() !== $position) {
                continue;
            }

            if ($asset instanceof ComponizerExternalCss || $asset instanceof ComponizerExternalJs) {
                echo $asset->toHtml($baseUrl);
            }

            echo $asset->toHtml();
        }
    }

    //-----------------------------------------------------
    // General private methods section
    //-----------------------------------------------------

    private function resetAssets(array &$collection)
    {
        $collection = [
            ComponizerAsset::TYPE_EXTERNAL_JS => [],
            ComponizerAsset::TYPE_INTERNAL_JS => [],
            ComponizerAsset::TYPE_EXTERNAL_CSS => [],
            ComponizerAsset::TYPE_INTERNAL_CSS => [],
        ];
    }

    private function collectAssets(array $assets, array &$collection)
    {
        foreach ($assets as $asset) {
            if ($asset instanceof ComponizerAsset && array_key_exists($asset->getType(), $collection)) {
                $collection[$asset->getType()][] = $asset;
            }
        }
    }

    private function getCollectedAssets(array &$collection)
    {
        return array_merge(
            $this->$collection[ComponizerAsset::TYPE_EXTERNAL_JS],
            $this->$collection[ComponizerAsset::TYPE_INTERNAL_JS],
            $this->$collection[ComponizerAsset::TYPE_EXTERNAL_CSS],
            $this->$collection[ComponizerAsset::TYPE_INTERNAL_CSS]
        );
    }

    private function containsAssets(array &$collection)
    {
        return (
            !empty($this->$collection[ComponizerAsset::TYPE_EXTERNAL_JS]) ||
            !empty($this->$collection[ComponizerAsset::TYPE_INTERNAL_JS]) ||
            !empty($this->$collection[ComponizerAsset::TYPE_EXTERNAL_CSS]) ||
            !empty($this->$collection[ComponizerAsset::TYPE_INTERNAL_CSS])
        );
    }

    private function getOption(array $options, $option)
    {
        return isset($options[$option]) ? $options[$option] : null;
    }

}