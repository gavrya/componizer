<?php
/**
 * Created by PhpStorm.
 * User: gavrya
 * Date: 10/26/15
 * Time: 4:54 PM
 */

namespace Gavrya\Componizer\Manager;


use Exception;
use Gavrya\Componizer\Componizer;
use Gavrya\Componizer\Helper\FsHelper;
use Gavrya\Componizer\Helper\StorageHelper;
use Gavrya\Componizer\Component\ComponentInterface;
use Gavrya\Componizer\Component\AbstractPluginComponent;

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
    public function getAllPlugins()
    {
        if ($this->plugins !== null) {
            return $this->plugins;
        }

        $plugins = [];

        /** @var FsHelper $fsHelper */
        $fsHelper = $this->componizer->resolve(FsHelper::class);

        // prepare plugins data
        $vendorPath = $fsHelper->composerVendorDir();

        if($vendorPath === null) {
            return [];
        }

        $jsonFiles = $fsHelper->pluginsJsonFiles($vendorPath, Componizer::PLUGIN_JSON_FILE_NAME);

        $jsonData = $fsHelper->pluginsJsonData($jsonFiles);

        // check plugin data
        foreach ($jsonData as $data) {
            // check version
            // todo: compare using version_compare() in future (http://php.net/manual/ru/function.version-compare.php)
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
            if (!$this->isPluginValid($plugin)) {
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
    public function findPlugin($plugin)
    {
        $pluginId = null;

        if ($plugin instanceof AbstractPluginComponent) {
            $pluginId = $plugin->getId();
        } elseif (is_string($plugin)) {
            $pluginId = $plugin;
        } else {
            return null;
        }

        $plugins = $this->getAllPlugins();

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
    public function getEnabledPlugins()
    {
        /** @var StorageHelper $storageHelper */
        $storageHelper = $this->componizer->resolve(StorageHelper::class);

        $plugins = $storageHelper->get('enabled_plugins', []);

        return array_intersect_key($this->getAllPlugins(), $plugins);
    }

    /**
     * Return all disabled plugins as array of $pluginId => $plugin.
     *
     * @return array
     */
    public function getDisabledPlugins()
    {
        return array_diff_key($this->getAllPlugins(), $this->getEnabledPlugins());
    }

    /**
     * Enable plugin by plugin instance or plugin id.
     * Enable plugins by array of plugin instances or plugin ids.
     *
     * @param $plugin
     * @return bool
     * @throws ComponizerException
     */
    public function enablePlugin($plugin)
    {
        // enable plugins using array
        if (is_array($plugin)) {
            foreach ($plugin as $item) {
                $this->enablePlugin($item);
            }

            return true;
        }

        // check plugin
        $plugin = $this->findPlugin($plugin);

        if ($plugin === null) {
            return false;
        }

        // save to storage
        try {
            /** @var StorageHelper $storageHelper */
            $storageHelper = $this->componizer->resolve(StorageHelper::class);

            $plugins = $storageHelper->get('enabled_plugins', []);

            // check existance
            if (isset($plugins[$plugin->id()])) {
                return true;
            }

            /** @var ComponentManager $componentManager */
            $componentManager = $this->componizer->resolve(ComponentManager::class);

            // enable component
            $componentManager->enableComponent($plugin);

            // enable plugin components
            foreach ($plugin->components() as $pluginComponent) {
                if ($pluginComponent instanceof ComponentInterface) {
                    $componentManager->enableComponent($pluginComponent);
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
    public function disablePlugin($plugin)
    {
        // disable plugins using array
        if (is_array($plugin)) {
            foreach ($plugin as $item) {
                $this->disablePlugin($item);
            }

            return true;
        }

        // check plugin
        $plugin = $this->findPlugin($plugin);

        if ($plugin === null) {
            return false;
        }

        // delete from storage
        try {
            /** @var StorageHelper $storageHelper */
            $storageHelper = $this->componizer->resolve(StorageHelper::class);

            $plugins = $storageHelper->get('enabled_plugins', []);

            // check existance
            if (!isset($plugins[$plugin->id()])) {
                return true;
            }

            /** @var ComponentManager $componentManager */
            $componentManager = $this->componizer->resolve(ComponentManager::class);

            // disable component
            $componentManager->disableComponent($plugin);

            // disable plugin components
            foreach ($plugin->components() as $pluginComponent) {
                if ($pluginComponent instanceof ComponentInterface) {
                    $componentManager->disableComponent($pluginComponent);
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
    public function isPluginEnabled($plugin)
    {
        $plugin = $this->findPlugin($plugin);

        if ($plugin === null) {
            return false;
        }

        $plugins = $this->getEnabledPlugins();

        return isset($plugins[$plugin->id()]);
    }

    //-----------------------------------------------------
    // Internal methods section
    //-----------------------------------------------------

    /**
     * Check if the plugin and all of its components is valid
     *
     * @param $plugin
     * @return bool
     */
    private function isPluginValid($plugin)
    {
        // check plugin instance
        if (!($plugin instanceof AbstractPluginComponent)) {
            return false;
        }

        /** @var ComponentManager $componentManager */
        $componentManager = $this->componizer->resolve(ComponentManager::class);

        // check plugin
        if (!$componentManager->isComponentValid($plugin)) {
            return false;
        }

        // plugin widgets
        $widgets = $plugin->getWidgets();

        if (!is_array($widgets)) {
            return false;
        }

        /** @var WidgetManager $widgetManager */
        $widgetManager = $this->componizer->resolve(WidgetManager::class);

        foreach ($widgets as $widget) {
            if (!$widgetManager->isWidgetValid($widget)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Init plugin and all of its components
     *
     * @param AbstractPluginComponent $plugin
     */
    private function initPlugin(AbstractPluginComponent $plugin)
    {
        /** @var ComponentManager $componentManager */
        $componentManager = $this->componizer->resolve(ComponentManager::class);

        // init plugin
        $componentManager->initComponent($plugin);

        // init plugin components
        foreach ($plugin->getComponents() as $pluginComponent) {
            if ($pluginComponent instanceof ComponentInterface) {
                $componentManager->initComponent($pluginComponent);
            }
        }
    }

}