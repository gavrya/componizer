<?php
/**
 * Created by PhpStorm.
 * User: gavrya
 * Date: 11/2/15
 * Time: 8:06 PM
 */

namespace Gavrya\Componizer\Managers;


use Gavrya\Componizer\Components\ComponentInterface;
use Gavrya\Componizer\Componizer;
use Gavrya\Componizer\ComponizerConfig;
use Gavrya\Componizer\Helpers\FsHelper;

/**
 * Class ComponentManager is used for component management.
 *
 * @package Gavrya\Componizer\Managers
 */
class ComponentManager
{

    /**
     * @var Componizer
     */
    private $componizer = null;

    //-----------------------------------------------------
    // Constructor section
    //-----------------------------------------------------

    /**
     * ComponentManager constructor.
     *
     * @param Componizer $componizer
     */
    public function __construct(Componizer $componizer)
    {
        $this->componizer = $componizer;
    }

    //-----------------------------------------------------
    // General methods section
    //-----------------------------------------------------

    /**
     * Tells if component valid.
     *
     * @param $component
     * @return bool
     */
    public function isComponentValid($component)
    {
        if (!($component instanceof ComponentInterface)) {
            return false;
        }

        $id = $component->getId();

        if (!is_string($id)) {
            return false;
        }

        if (!preg_match('/^[a-f0-9]{8}$/', $id)) {
            return false;
        }

        $name = $component->getName();

        if (!is_string($name) || empty(trim($name))) {
            return false;
        }

        $version = $component->getVersion();

        if (!is_string($version) || empty(trim($version))) {
            return false;
        }

        $info = $component->getInfo();

        if (!is_string($info) || empty(trim($info))) {
            return false;
        }

        $assetsDir = $component->getAssetsDir();

        if ($component->hasAssets() && !is_string($assetsDir)) {
            return false;
        }

        if ($component->hasAssets() && (!file_exists($assetsDir) || !is_dir($assetsDir))) {
            return false;
        }

        if ($component->hasAssets() && $assetsDir !== realpath($assetsDir)) {
            return false;
        }

        if ($component->hasAssets() && basename($assetsDir) !== $id) {
            return false;
        }

        return true;
    }

    /**
     * Initiates component.
     *
     * @param ComponentInterface $component
     */
    public function initComponent(ComponentInterface $component)
    {
        $componentCacheDir = $this->createComponentCacheDir($component);

        $this->syncComponentAssetsDir($component);

        $config = $this->componizer->getConfig();

        $lang = $config->get(ComponizerConfig::CONFIG_LANG);

        $component->init($lang, $componentCacheDir);
    }

    /**
     * Enables component.
     *
     * @param ComponentInterface $component
     */
    public function enableComponent(ComponentInterface $component)
    {
        $this->createComponentCacheDir($component);

        $this->syncComponentAssetsDir($component);

        $component->up();
    }

    /**
     * Disables component.
     *
     * @param ComponentInterface $component
     */
    public function disableComponent(ComponentInterface $component)
    {
        $component->down();

        $this->unsyncComponentAssetsDir($component);

        $this->removeComponentCacheDir($component);
    }

    /**
     * Creates component cache dir.
     *
     * @param ComponentInterface $component
     * @return string
     */
    private function createComponentCacheDir(ComponentInterface $component)
    {
        $config = $this->componizer->getConfig();

        $cacheDir = $config->get(ComponizerConfig::CONFIG_CACHE_DIR);

        $componentCacheDir = $cacheDir . DIRECTORY_SEPARATOR . $component->getId();

        /** @var FsHelper $fsHelper */
        $fsHelper = $this->componizer->resolve(FsHelper::class);

        $fsHelper->makeDir($componentCacheDir);

        return $componentCacheDir;
    }

    /**
     * Removes component cache dir.
     *
     * @param ComponentInterface $component
     */
    private function removeComponentCacheDir(ComponentInterface $component)
    {
        $config = $this->componizer->getConfig();

        $cacheDir = $config->get(ComponizerConfig::CONFIG_CACHE_DIR);

        $componentCacheDir = $cacheDir . DIRECTORY_SEPARATOR . $component->getId();

        /** @var FsHelper $fsHelper */
        $fsHelper = $this->componizer->resolve(FsHelper::class);

        $fsHelper->removeDir($componentCacheDir);
    }

    /**
     * Syncs component assets dir.
     *
     * @param ComponentInterface $component
     * @return string|void
     */
    private function syncComponentAssetsDir(ComponentInterface $component)
    {
        if (!$component->hasAssets()) {
            return;
        }

        $config = $this->componizer->getConfig();

        $publicDir = $config->get(ComponizerConfig::CONFIG_PUBLIC_DIR);

        $targetLink = $publicDir . DIRECTORY_SEPARATOR . $component->getId();

        /** @var FsHelper $fsHelper */
        $fsHelper = $this->componizer->resolve(FsHelper::class);

        $fsHelper->createSymlink($component->getAssetsDir(), $targetLink);

        return $targetLink;
    }

    /**
     * Unsyncs component assets dir.
     *
     * @param ComponentInterface $component
     */
    private function unsyncComponentAssetsDir(ComponentInterface $component)
    {
        if (!$component->hasAssets()) {
            return;
        }

        $config = $this->componizer->getConfig();

        $publicDir = $config->get(ComponizerConfig::CONFIG_PUBLIC_DIR);

        $targetLink = $publicDir . DIRECTORY_SEPARATOR . $component->getId();

        /** @var FsHelper $fsHelper */
        $fsHelper = $this->componizer->resolve(FsHelper::class);

        $fsHelper->removeSymlink($targetLink);
    }

}