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
use Gavrya\Componizer\Skeleton\ComponizerWidget;

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

        // public dir
        $publicDir = $config[Componizer::CONFIG_PUBLIC_DIR];

        // FsHelper
        $fsHelper = $this->componizer->resolve(FsHelper::class);

        // remove broken symlinks
        $fsHelper->removeBrokenSymlinks($publicDir);

        // storage helper
        $storageHelper = $this->componizer->resolve(StorageHelper::class);

        // enabled plugins by config
        $plugins = $storageHelper->get('enabled_plugins', []);

        // remove broken cache dirs
        $fsHelper->removeDirs($cacheDir, array_keys($plugins));
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

            // create plugin instance from class name (may throw FatalException if not loadable)
            $plugin = new $data['plugin']['class'];

            // validate plugin and its components
            if (!$this->validPlugin($plugin)) {
                continue;
            }

            // init plugin
            $this->initPlugin($plugin);

            // add plugin to available plugins
            $plugins[$plugin->id()] = $plugin;
        }

        return $this->plugins = $plugins;
    }

    public function find($plugin)
    {
        $pluginId = null;

        if ($plugin instanceof ComponizerComponent && $plugin instanceof ComponizerPlugin) {
            $pluginId = $plugin->id();
        } elseif (is_string($plugin)) {
            $pluginId = $plugin;
        } else {
            return null;
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
        // enable plugins using array
        if (is_array($plugin) && !empty($plugin)) {
            foreach ($plugin as $item) {
                // check instance
                if ($item instanceof ComponizerComponent && $item instanceof ComponizerPlugin) {
                    // enable plugin
                    $this->enable($item);
                }
            }

            return true;
        }

        // check plugin
        $plugin = $this->find($plugin);

        if ($plugin === null) {
            return false;
        }

        // save to storage
        try {
            $storageHelper = $this->componizer->resolve(StorageHelper::class);

            $plugins = $storageHelper->get('enabled_plugins', []);

            // check existance
            if (isset($plugins[$plugin->id()])) {
                return true;
            }

            // component manager
            $componentManager = $this->componizer->resolve(ComponentManager::class);

            // enable component
            $componentManager->enable($plugin);

            // enable plugin components
            foreach ($plugin->components() as $pluginComponent) {
                if ($pluginComponent instanceof ComponizerComponent) {
                    $componentManager->enable($pluginComponent);
                }
            }

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
        // disable plugins using array
        if (is_array($plugin) && !empty($plugin)) {
            foreach ($plugin as $item) {
                // check instance
                if ($item instanceof ComponizerComponent && $item instanceof ComponizerPlugin) {
                    // disable plugin
                    $this->disable($item);
                }
            }

            return true;
        }

        // check plugin
        $plugin = $this->find($plugin);

        if ($plugin === null) {
            return false;
        }

        // delete from storage
        try {
            $storageHelper = $this->componizer->resolve(StorageHelper::class);

            $plugins = $storageHelper->get('enabled_plugins', []);

            // check existance
            if (!isset($plugins[$plugin->id()])) {
                return true;
            }

            // component manager
            $componentManager = $this->componizer->resolve(ComponentManager::class);

            // disable component
            $componentManager->disable($plugin);

            // disable plugin components
            foreach ($plugin->components() as $pluginComponent) {
                if ($pluginComponent instanceof ComponizerComponent) {
                    $componentManager->disable($pluginComponent);
                }
            }

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

    public function isEnabled($plugin, $checkConfig = false)
    {
        $plugin = $this->find($plugin);

        if($plugin === null) {
            return false;
        }

        $plugins = $this->enabled();

        return isset($plugins[$plugin->id()]);
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

        // component manager
        $componentManager = $this->componizer->resolve(ComponentManager::class);

        // check component
        if (!$componentManager->valid($plugin)) {
            return false;
        }

        // plugin widgets
        $widgets = $plugin->widgets();

        if (!is_array($widgets)) {
            return false;
        }

        foreach ($widgets as $widget) {
            // check instance
            if (!($widget instanceof ComponizerComponent && $widget instanceof ComponizerWidget)) {
                return false;
            }

            // validate component
            if(!$componentManager->valid($widget)) {
                return false;
            }

            // check contains existed widget
            if (!$plugin->hasWidget($widget)) {
                return false;
            }
        }

        return true;
    }

    private function initPlugin(ComponizerPlugin $plugin)
    {
        // component manager
        $componentManager = $this->componizer->resolve(ComponentManager::class);

        // init component
        $componentManager->init($plugin);

        // init plugin components
        foreach ($plugin->components() as $pluginComponent) {
            if ($pluginComponent instanceof ComponizerComponent) {
                $componentManager->init($pluginComponent);
            }
        }
    }

}