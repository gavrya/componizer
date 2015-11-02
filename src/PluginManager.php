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

        // TODO: remove broken cache dirs ???
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

            // validate plugin
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
        // enable plugins using array
        if (is_array($plugin) && !empty($plugin)) {
            foreach ($plugin as $item) {
                // check instance
                if (!($item instanceof ComponizerComponent && $item instanceof ComponizerPlugin)) {
                    return false;
                }
                // enable plugin
                if ($this->enable($item) === false) {
                    return false;
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
                return false;
            }

            // component manager
            $componentManager = $this->componizer->resolve(ComponentManager::class);

            // enable component
            $componentManager->enable($plugin);

            // enable plugin components
            $pluginComponents = array_merge($plugin->widgets());
            foreach ($pluginComponents as $pluginComponent) {
                if ($pluginComponent instanceof ComponizerComponent && $componentManager->valid($pluginComponent)) {
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
                if (!($item instanceof ComponizerComponent && $item instanceof ComponizerPlugin)) {
                    return false;
                }
                // disable plugin
                if ($this->disable($item) === false) {
                    return false;
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
                return false;
            }

            // component manager
            $componentManager = $this->componizer->resolve(ComponentManager::class);

            // disable component
            $componentManager->disable($plugin);

            // disable plugin components
            $pluginComponents = array_merge($plugin->widgets());
            foreach ($pluginComponents as $pluginComponent) {
                if ($pluginComponent instanceof ComponizerComponent && $componentManager->valid($pluginComponent)) {
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

    public function isEnabled($plugin)
    {
        $plugin = $this->find($plugin);
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

        // component manager
        $componentManager = $this->componizer->resolve(ComponentManager::class);

        // check component
        if (!$componentManager->valid($plugin)) {
            return false;
        }

        // plugin widgets
        $widgets = $plugin->widgets();
        $countWidgets = $plugin->countWidgets();

        if (!is_array($widgets)) {
            return false;
        }

        if (count($widgets) !== $countWidgets) {
            return false;
        }

        if ($plugin->hasWidgets() !== (bool) $countWidgets) {
            return false;
        }

        foreach ($widgets as $widget) {
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
        // component manager
        $componentManager = $this->componizer->resolve(ComponentManager::class);

        // init component
        $componentManager->init($plugin);

        // init plugin components
        $pluginComponents = array_merge($plugin->widgets());
        foreach ($pluginComponents as $pluginComponent) {
            if ($pluginComponent instanceof ComponizerComponent && $componentManager->valid($pluginComponent)) {
                $componentManager->init($pluginComponent);
            }
        }
    }

}