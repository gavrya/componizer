<?php
/**
 * Created by PhpStorm.
 * User: gavrya
 * Date: 11/2/15
 * Time: 8:06 PM
 */

namespace Gavrya\Componizer;


use Gavrya\Componizer\Helper\FsHelper;
use Gavrya\Componizer\Skeleton\ComponizerComponent;

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

    public function isValid($component)
    {
        if (!($component instanceof ComponizerComponent)) {
            return false;
        }

        // check id
        $id = $component->id();

        if (!is_string($id)) {
            return false;
        }

        if (!preg_match('/^[a-f0-9]{8}$/', $id)) {
            return false;
        }

        // check name
        $name = $component->name();

        if (!is_string($name) || empty(trim($name))) {
            return false;
        }

        // check version
        $version = $component->version();

        if (!is_string($version) || empty(trim($version))) {
            return false;
        }

        // check info
        $info = $component->info();

        if (!is_string($info) || empty(trim($info))) {
            return false;
        }

        // assets dir
        $assetsDir = $component->assetsDir();

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

    public function init(ComponizerComponent $component)
    {
        // create component cache dir
        $componentCacheDir = $this->createCacheDir($component);

        // sync assets
        $this->syncAssetsDir($component);

        // componizer config
        $config = $this->componizer->config();

        // lang
        $lang = $config[Componizer::CONFIG_LANG];

        // init component
        $component->init($lang, $componentCacheDir);
    }

    public function enable(ComponizerComponent $component)
    {
        // create component cache dir
        $this->createCacheDir($component);

        // sync assets
        $this->syncAssetsDir($component);

        // call up() method
        $component->up();
    }

    public function disable(ComponizerComponent $component)
    {
        // call down() method
        $component->down();

        // unsync assets
        $this->unsyncAssetsDir($component);

        // remove component cache dir
        $this->removeCacheDir($component);
    }

    private function createCacheDir(ComponizerComponent $component)
    {
        // componizer config
        $config = $this->componizer->config();

        // cache dir
        $cacheDir = $config[Componizer::CONFIG_CACHE_DIR];

        // component cache dir
        $componentCacheDir = $cacheDir . DIRECTORY_SEPARATOR . $component->id();

        /** @var FsHelper $fsHelper */
        $fsHelper = $this->componizer->resolve(FsHelper::class);

        // make dir
        $fsHelper->makeDir($componentCacheDir);

        return $componentCacheDir;
    }

    private function removeCacheDir(ComponizerComponent $component)
    {
        // componizer config
        $config = $this->componizer->config();

        // cache dir
        $cacheDir = $config[Componizer::CONFIG_CACHE_DIR];

        // component cache dir
        $componentCacheDir = $cacheDir . DIRECTORY_SEPARATOR . $component->id();

        /** @var FsHelper $fsHelper */
        $fsHelper = $this->componizer->resolve(FsHelper::class);

        // remove dir
        $fsHelper->removeDir($componentCacheDir);
    }

    private function syncAssetsDir(ComponizerComponent $component)
    {
        if (!$component->hasAssets()) {
            return;
        }

        // componizer config
        $config = $this->componizer->config();

        // public dir
        $publicDir = $config[Componizer::CONFIG_PUBLIC_DIR];

        // component public dir symlink
        $targetLink = $publicDir . DIRECTORY_SEPARATOR . $component->id();

        /** @var FsHelper $fsHelper */
        $fsHelper = $this->componizer->resolve(FsHelper::class);

        // create symlink
        $fsHelper->createSymlink($component->assetsDir(), $targetLink);

        return $targetLink;
    }

    private function unsyncAssetsDir(ComponizerComponent $component)
    {
        if (!$component->hasAssets()) {
            return;
        }

        // componizer config
        $config = $this->componizer->config();

        // public dir
        $publicDir = $config[Componizer::CONFIG_PUBLIC_DIR];

        // component public dir symlink
        $targetLink = $publicDir . DIRECTORY_SEPARATOR . $component->id();

        /** @var FsHelper $fsHelper */
        $fsHelper = $this->componizer->resolve(FsHelper::class);

        // remove symlink
        $fsHelper->removeSymlink($targetLink);
    }

}