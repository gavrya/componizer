<?php
/**
 * Created by PhpStorm.
 * User: gavrya
 * Date: 10/26/15
 * Time: 4:54 PM
 */

namespace Gavrya\Componizer;


use Exception;
use Gavrya\Componizer\Helper\FsHelper;
use Gavrya\Componizer\Helper\StorageHelper;
use Gavrya\Componizer\Skeleton\ComponizerComponent;
use Gavrya\Componizer\Skeleton\ComponizerException;
use Gavrya\Componizer\Skeleton\ComponizerPlugin;
use Gavrya\Componizer\Skeleton\ComponizerPluginManager;

class PluginManager implements ComponizerPluginManager
{

    // Componizer
    private $componizer = null;

    // Plugins
    private $plugins = null;

    //-----------------------------------------------------
    // Instance creation/init section
    //-----------------------------------------------------

    public function __construct(Componizer $componizer)
    {
        $this->componizer = $componizer;
        $this->init();
    }

    private function init()
    {
        // componizer config
        $config = $this->componizer->config();

        // cache dir
        $cacheDir = $config[Componizer::CONFIG_CACHE_DIR];

        // FsHelper
        $fsHelper = $this->componizer->resolve(FsHelper::class);

        // remove broken symlinks
        $fsHelper->removeBrokenSymlinks($cacheDir);
    }

    //-----------------------------------------------------
    // ComponizerPluginManager implementation section
    //-----------------------------------------------------

    public function all()
    {
        if ($this->plugins !== null) {
            return $this->plugins;
        }

        $plugins = [];
        $fsHelper = $this->componizer->resolve(FsHelper::class);

        // prepare plugins data
        $vendorPath = $fsHelper->composerVendorDir();
        $jsonFiles = $fsHelper->pluginsJsonFiles($vendorPath, Componizer::PLUGIN_JSON_FILE_NAME);
        $jsonData = $fsHelper->pluginsJsonData($jsonFiles);

        // check plugin data
        foreach ($jsonData as $data) {
            // check version
            if (!isset($data['componizer']['version']) || $data['componizer']['version'] !== Componizer::VERSION) {
                continue;
            }

            // check plugin class
            if (!isset($data['plugin']['class'])) {
                continue;
            }

            // create plugin instance from class name
            // this action may throw FatalException if class does not exists or it cant be loaded by any autoloader
            $plugin = new $data['plugin']['class'];

            // validate plugin
            if (!$this->validPlugin($plugin)) {
                // throw ComponizerException ???
                continue;
            }

            // init plugin
            $this->initPlugin($plugin);

            // add plugin to available plugins
            $plugins[$plugin->id()] = $plugin;
        }

        return $this->plugins = $plugins;
    }

    public function get($plugin)
    {
        $pluginId = null;
        if ($plugin instanceof ComponizerComponent && $plugin instanceof ComponizerPlugin) {
            $pluginId = $plugin->id();
        } elseif (is_string($plugin) || is_numeric($plugin)) {
            $pluginId = $plugin;
        } else {
            return $pluginId;
        }

        $plugins = $this->all();

        return isset($plugins[$pluginId]) ? $plugins[$pluginId] : null;
    }

    public function enabled()
    {
        $storageHelper = $this->componizer->resolve(StorageHelper::class);
        $plugins = $storageHelper->get('enabled_plugins', []);

        return array_intersect_key($this->all(), $plugins);
    }

    public function disabled()
    {
        return array_diff_key($this->all(), $this->enabled());
    }

    public function enable($plugin)
    {
        // check plugin
        $plugin = $this->get($plugin);
        if ($plugin === null) {
            return false;
        }

        // save to storage
        try {
            $storageHelper = $this->componizer->resolve(StorageHelper::class);
            $plugins = $storageHelper->get('enabled_plugins', []);

            // check existance
            if (isset($plugins[$plugin->id()])) {
                return false;
            }

            // enable plugin component
            $this->enableComponent($plugin);

            // update enabled plugins
            $plugins[$plugin->id()] = get_class($plugin);

            // update storage
            $storageHelper->set('enabled_plugins', $plugins);
            $storageHelper->save();

            return true;
        } catch (Exception $ex) {
            throw new ComponizerException('Unable to enable plugin with id: ' . $plugin->id());
        }

        return false;
    }

    public function disable($plugin)
    {
        // check plugin
        $plugin = $this->get($plugin);
        if ($plugin === null) {
            return false;
        }

        // delete from storage
        try {
            $storageHelper = $this->componizer->resolve(StorageHelper::class);
            $plugins = $storageHelper->get('enabled_plugins', []);

            // check existance
            if (!isset($plugins[$plugin->id()])) {
                return false;
            }

            // disable plugin component
            $this->disableComponent($plugin);

            // update enabled plugins
            unset($plugins[$plugin->id()]);

            // update storage
            $storageHelper->set('enabled_plugins', $plugins);
            $storageHelper->save();

            return true;
        } catch (Exception $ex) {
            throw new ComponizerException('Unable to disable plugin with id: ' . $plugin->id());
        }

        return false;
    }

    public function isEnabled($plugin)
    {
        $plugin = $this->get($plugin);
        $plugins = $this->enabled();

        return $plugin !== null && isset($plugins[$plugin->id()]);
    }

    //-----------------------------------------------------
    // Helpers section
    //-----------------------------------------------------

    private function validPlugin($plugin)
    {
        // check plugin instance
        if (!($plugin instanceof ComponizerComponent && $plugin instanceof ComponizerPlugin)) {
            return false;
        }

        // check component
        if(!$this->validComponent($plugin)) {
            return false;
        }

        // check plugin widgets related data

        if (!is_array($plugin->widgets())) {
            return false;
        }

        if (count($plugin->widgets()) !== $plugin->countWidgets()) {
            return false;
        }

        if ($plugin->hasWidgets() !== (bool) $plugin->countWidgets()) {
            return false;
        }

        foreach ($plugin->widgets() as $widget) {
            if (!($widget instanceof ComponizerComponent)) { // && instanceof ComponizerWidget
                return false;
            }

            if (!$plugin->hasWidget($widget)) {
                return false;
            }
        }

        return true;
    }

    private function initPlugin($plugin)
    {
        $this->initComponent($plugin);

        // init plugin components
        $pluginComponents = array_merge($plugin->widgets());
        foreach ($pluginComponents as $pluginComponent) {
            if ($pluginComponent instanceof ComponizerComponent && $this->validComponent($pluginComponent)) {
                $this->initComponent($pluginComponent);
            }
        }
    }

    private function validComponent($component)
    {
        // check instance
        if (!($component instanceof ComponizerComponent)) {
            return false;
        }

        // check component id (lowercase md5)
        if (!preg_match('/^[a-f0-9]{32}$/', $component->id())) {
            return false;
        }

        // check assets dir
        if ($component->hasAssets() && !is_dir($component->assetsDir())) {
            return false;
        }

        // check assets dir real path
        if ($component->hasAssets() && $component->assetsDir() !== realpath($component->assetsDir())) {
            return false;
        }

        // check assets dir name
        if ($component->hasAssets() && basename($component->assetsDir()) !== $component->id()) {
            return false;
        }

        return true;
    }

    private function initComponent(ComponizerComponent $component)
    {
        // componizer config
        $config = $this->componizer->config();

        // lang
        $lang = $config[Componizer::CONFIG_LANG];

        // cache dir
        $cacheDir = $config[Componizer::CONFIG_CACHE_DIR];

        // public dir
        $publicDir = $config[Componizer::CONFIG_PUBLIC_DIR];

        // create component cache dir
        $componentCacheDir = $cacheDir . DIRECTORY_SEPARATOR . $component->id();
        if (!is_dir($componentCacheDir)) {
            mkdir($componentCacheDir);
        }

        // create public dir symlink
        if ($component->hasAssets()) {
            // FsHelper
            $fsHelper = $this->componizer->resolve(FsHelper::class);

            // component public dir symlink
            $targetLink = $publicDir . DIRECTORY_SEPARATOR . $component->id();

            // create symlink
            $fsHelper->createSymlink($component->assetsDir(), $targetLink);
        }

        // init component
        $component->init($lang);
    }

    private function enableComponent(ComponizerComponent $component)
    {
        // sync assets dir

        // call up() method

        // if plugin, also enable plugin components
    }

    private function disableComponent(ComponizerComponent $component)
    {
        // unsync assets dir

        // call down() method

        // if plugin, also disable plugin components
    }

}