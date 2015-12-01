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
use Gavrya\Componizer\Skeleton\ComponizerAssets;
use Gavrya\Componizer\Skeleton\ComponizerComponent;
use Gavrya\Componizer\Skeleton\ComponizerException;
use Gavrya\Componizer\Skeleton\ComponizerPlugin;
use Gavrya\Componizer\Skeleton\ComponizerWidget;

class PluginManager
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
    }

    //-----------------------------------------------------
    // Get section
    //-----------------------------------------------------

    /**
     * Return array of all available plugins as $pluginId => $plugin.
     *
     * @return array|null
     */
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

    /**
     * Find plugin by plugin id or plugin instance.
     *
     * @param $plugin
     * @return null
     */
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

    //-----------------------------------------------------
    // Enable/Disable section
    //-----------------------------------------------------

    /**
     * Return all enabled plugins as array of $pluginId => $plugin.
     *
     * @return array
     */
    public function enabled()
    {
        $storageHelper = $this->componizer->resolve(StorageHelper::class);

        $plugins = $storageHelper->get('enabled_plugins', []);

        return array_intersect_key($this->all(), $plugins);
    }

    /**
     * Return all disabled plugins as array of $pluginId => $plugin.
     *
     * @return array
     */
    public function disabled()
    {
        return array_diff_key($this->all(), $this->enabled());
    }

    /**
     * Enable plugin by plugin instance or plugin id.
     * Enable plugins by array of plugin instances or plugin ids.
     *
     * @param $plugin
     * @return bool
     * @throws ComponizerException
     */
    public function enable($plugin)
    {
        // enable plugins using array
        if (is_array($plugin)) {
            foreach ($plugin as $item) {
                $this->enable($item);
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

    /**
     * Disable plugin by plugin instance or plugin id.
     * Disable plugins by array of plugin instances or plugin ids.
     *
     * @param $plugin
     * @return bool
     * @throws ComponizerException
     */
    public function disable($plugin)
    {
        // disable plugins using array
        if (is_array($plugin)) {
            foreach ($plugin as $item) {
                $this->disable($item);
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

            // unset enabled plugin
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

    /**
     * Check if the plugin is enabled by plugin inatance or plugin id.
     *
     * @param $plugin
     * @return bool
     */
    public function isEnabled($plugin)
    {
        $plugin = $this->find($plugin);

        if ($plugin === null) {
            return false;
        }

        $plugins = $this->enabled();

        return isset($plugins[$plugin->id()]);
    }

    //-----------------------------------------------------
    // Allow/Deny section
    //-----------------------------------------------------

    // based on current scope

    //public function allowed();
    //public function denied();
    //public function isAllowed($plugin);

    //-----------------------------------------------------
    // Internal methods section
    //-----------------------------------------------------

    /**
     * Check if the plugin and all of its components is valid
     *
     * @param $plugin
     * @return bool
     */
    private function validPlugin($plugin)
    {
        // check plugin instance
        if (!($plugin instanceof ComponizerComponent && $plugin instanceof ComponizerPlugin)) {
            return false;
        }

        // component manager
        $componentManager = $this->componizer->resolve(ComponentManager::class);

        // check plugin
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
            if (!$componentManager->valid($widget)) {
                return false;
            }

            // check widget editor assets
            if(!($widget->editorAssets() instanceof ComponizerAssets)) {
                // todo: check if empty
                return false;
            }

            // check widget display assets
            if(!($widget->displayAssets() instanceof ComponizerAssets)) {
                // todo: check if empty
                return false;
            }
        }

        return true;
    }

    /**
     * Init plugin and all of its components
     *
     * @param ComponizerPlugin $plugin
     */
    private function initPlugin(ComponizerPlugin $plugin)
    {
        // component manager
        $componentManager = $this->componizer->resolve(ComponentManager::class);

        // init plugin
        $componentManager->init($plugin);

        // init plugin components
        foreach ($plugin->components() as $pluginComponent) {
            if ($pluginComponent instanceof ComponizerComponent) {
                $componentManager->init($pluginComponent);
            }
        }
    }

}