<?php
/**
 * Created by PhpStorm.
 * User: gavrya
 * Date: 11/2/15
 * Time: 8:06 PM
 */

namespace Gavrya\Componizer\Manager;


use Gavrya\Componizer\Componizer;
use Gavrya\Componizer\Config;
use Gavrya\Componizer\Helper\FsHelper;
use Gavrya\Componizer\Component\ComponentInterface;

class ComponentManager
{

    // Componizer
    private $componizer = null;

    //-----------------------------------------------------
    // Instance creation/init section
    //-----------------------------------------------------

    public function __construct(Componizer $componizer)
    {
        $this->componizer = $componizer;
    }

    //-----------------------------------------------------
    // Methods implementation section
    //-----------------------------------------------------

    public function isComponentValid($component)
    {
        if (!($component instanceof ComponentInterface)) {
            return false;
        }

        // check id
        $id = $component->getId();

        if (!is_string($id)) {
            return false;
        }

        if (!preg_match('/^[a-f0-9]{8}$/', $id)) {
            return false;
        }

        // check name
        $name = $component->getName();

        if (!is_string($name) || empty(trim($name))) {
            return false;
        }

        // check version
        $version = $component->getVersion();

        if (!is_string($version) || empty(trim($version))) {
            return false;
        }

        // check info
        $info = $component->getInfo();

        if (!is_string($info) || empty(trim($info))) {
            return false;
        }

        // assets dir
        $assetsDir = $component->getAssetsDir();

        if ($component->hasAssets() && !is_string($assetsDir)) {
            return false;
        }

        // check assets dir
        if ($component->hasAssets() && (!file_exists($assetsDir) || !is_dir($assetsDir))) {
            return false;
        }

        // check assets dir real path
        if ($component->hasAssets() && $assetsDir !== realpath($assetsDir)) {
            return false;
        }

        // check assets dir name = component id
        if ($component->hasAssets() && basename($assetsDir) !== $id) {
            return false;
        }

        return true;
    }

    public function initComponent(ComponentInterface $component)
    {
        // create component cache dir
        $componentCacheDir = $this->createComponentCacheDir($component);

        // sync assets
        $this->syncComponentAssetsDir($component);

        // componizer config
        $config = $this->componizer->getConfig();

        // lang
        $lang = $config->get(Config::CONFIG_LANG);

        // init component
        $component->init($lang, $componentCacheDir);
    }

    public function enableComponent(ComponentInterface $component)
    {
        // create component cache dir
        $this->createComponentCacheDir($component);

        // sync assets
        $this->syncComponentAssetsDir($component);

        // call up() method
        $component->up();
    }

    public function disableComponent(ComponentInterface $component)
    {
        // call down() method
        $component->down();

        // unsync assets
        $this->unsyncComponentAssetsDir($component);

        // remove component cache dir
        $this->removeComponentCacheDir($component);
    }

    private function createComponentCacheDir(ComponentInterface $component)
    {
        // componizer config
        $config = $this->componizer->getConfig();

        // cache dir
        $cacheDir = $config->get(Config::CONFIG_CACHE_DIR);

        // component cache dir
        $componentCacheDir = $cacheDir . DIRECTORY_SEPARATOR . $component->getId();

        /** @var FsHelper $fsHelper */
        $fsHelper = $this->componizer->resolve(FsHelper::class);

        // make dir
        $fsHelper->makeDir($componentCacheDir);

        return $componentCacheDir;
    }

    private function removeComponentCacheDir(ComponentInterface $component)
    {
        // componizer config
        $config = $this->componizer->getConfig();

        // cache dir
        $cacheDir = $config->get(Config::CONFIG_CACHE_DIR);

        // component cache dir
        $componentCacheDir = $cacheDir . DIRECTORY_SEPARATOR . $component->getId();

        /** @var FsHelper $fsHelper */
        $fsHelper = $this->componizer->resolve(FsHelper::class);

        // remove dir
        $fsHelper->removeDir($componentCacheDir);
    }

    private function syncComponentAssetsDir(ComponentInterface $component)
    {
        if (!$component->hasAssets()) {
            return;
        }

        // componizer config
        $config = $this->componizer->getConfig();

        // public dir
        $publicDir = $config->get(Config::CONFIG_PUBLIC_DIR);

        // component public dir symlink
        $targetLink = $publicDir . DIRECTORY_SEPARATOR . $component->getId();

        /** @var FsHelper $fsHelper */
        $fsHelper = $this->componizer->resolve(FsHelper::class);

        // create symlink
        $fsHelper->createSymlink($component->getAssetsDir(), $targetLink);

        return $targetLink;
    }

    private function unsyncComponentAssetsDir(ComponentInterface $component)
    {
        if (!$component->hasAssets()) {
            return;
        }

        // componizer config
        $config = $this->componizer->getConfig();

        // public dir
        $publicDir = $config->get(Config::CONFIG_PUBLIC_DIR);

        // component public dir symlink
        $targetLink = $publicDir . DIRECTORY_SEPARATOR . $component->getId();

        /** @var FsHelper $fsHelper */
        $fsHelper = $this->componizer->resolve(FsHelper::class);

        // remove symlink
        $fsHelper->removeSymlink($targetLink);
    }

}