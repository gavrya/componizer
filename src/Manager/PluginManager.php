<?php
/**
 * Created by PhpStorm.
 * User: gavrya
 * Date: 10/26/15
 * Time: 4:54 PM
 */

namespace Gavrya\Componizer\Manager;


use Exception;
use Gavrya\Componizer\Component\AbstractPluginComponent;
use Gavrya\Componizer\Component\ComponentInterface;
use Gavrya\Componizer\Componizer;
use Gavrya\Componizer\Helper\FsHelper;
use Gavrya\Componizer\Helper\StorageHelper;

/**
 * Class PluginManager is used for plugins management.
 *
 * @package Gavrya\Componizer\Manager
 */
class PluginManager
{

    // Storage key constant
    const STORAGE_ENABLED_PLUGINS = 'enabled_plugins';

    // Plugin JSON constants
    const JSON_VAR_COMPONIZER = 'componizer';
    const JSON_VAR_VERSION = 'version';
    const JSON_VAR_PLUGIN = 'plugin';
    const JSON_VAR_CLASS = 'class';

    /**
     * @var Componizer
     */
    private $componizer = null;

    /**
     * @var AbstractPluginComponent[]
     */
    private $plugins = null;

    //-----------------------------------------------------
    // Constructor section
    //-----------------------------------------------------

    /**
     * PluginManager constructor.
     *
     * @param Componizer $componizer
     */
    public function __construct(Componizer $componizer)
    {
        $this->componizer = $componizer;
    }

    //-----------------------------------------------------
    // Get/find section
    //-----------------------------------------------------

    /**
     * Returns all available valid plugins.
     *
     * @return AbstractPluginComponent[]
     */
    public function getAllPlugins()
    {
        if ($this->plugins !== null) {
            return $this->plugins;
        }

        $plugins = [];

        /** @var FsHelper $fsHelper */
        $fsHelper = $this->componizer->resolve(FsHelper::class);

        $vendorPath = $fsHelper->getComposerVendorDir();

        if ($vendorPath === null) {
            return [];
        }

        $pluginsJsonFiles = $fsHelper->getPluginsJsonFiles($vendorPath, Componizer::PLUGIN_JSON_FILE_NAME);

        $pluginsJsonData = $fsHelper->getPluginsJsonData($pluginsJsonFiles);

        foreach ($pluginsJsonData as $pluginJsonData) {
            if (!isset($pluginJsonData[static::JSON_VAR_COMPONIZER][static::JSON_VAR_VERSION])) {
                continue;
            }

            // todo: compare using version_compare()
            if ($pluginJsonData[static::JSON_VAR_COMPONIZER][static::JSON_VAR_VERSION] !== Componizer::VERSION) {
                continue;
            }

            if (!isset($pluginJsonData[static::JSON_VAR_PLUGIN][static::JSON_VAR_CLASS])) {
                continue;
            }

            // creates plugin instance from class name (may throw FatalException if not loadable)

            /** @var AbstractPluginComponent $plugin */
            $plugin = new $pluginJsonData[static::JSON_VAR_PLUGIN][static::JSON_VAR_CLASS];

            if (!$this->isPluginValid($plugin)) {
                continue;
            }

            $this->initPlugin($plugin);

            $plugins[$plugin->getId()] = $plugin;
        }

        return $this->plugins = $plugins;
    }

    /**
     * Finds available plugin by id or instance.
     *
     * @param AbstractPluginComponent|string $plugin
     * @return AbstractPluginComponent|null
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
     * Returns all enabled plugins.
     *
     * @return AbstractPluginComponent[]
     */
    public function getEnabledPlugins()
    {
        /** @var StorageHelper $storageHelper */
        $storageHelper = $this->componizer->resolve(StorageHelper::class);

        $plugins = $storageHelper->get(static::STORAGE_ENABLED_PLUGINS, []);

        return array_intersect_key($this->getAllPlugins(), $plugins);
    }

    /**
     * Returns all disabled plugins.
     *
     * @return AbstractPluginComponent[]
     */
    public function getDisabledPlugins()
    {
        return array_diff_key($this->getAllPlugins(), $this->getEnabledPlugins());
    }

    /**
     * Enables plugin by id or instance.
     *
     * @param AbstractPluginComponent|AbstractPluginComponent[]|string $plugin
     * @return bool
     */
    public function enablePlugin($plugin)
    {
        if (is_array($plugin)) {
            foreach ($plugin as $item) {
                $this->enablePlugin($item);
            }

            return true;
        }

        $plugin = $this->findPlugin($plugin);

        if ($plugin === null) {
            return false;
        }

        try {
            /** @var StorageHelper $storageHelper */
            $storageHelper = $this->componizer->resolve(StorageHelper::class);

            $enabledPlugins = $storageHelper->get(static::STORAGE_ENABLED_PLUGINS, []);

            if (isset($enabledPlugins[$plugin->getId()])) {
                return true;
            }

            /** @var ComponentManager $componentManager */
            $componentManager = $this->componizer->resolve(ComponentManager::class);

            $componentManager->enableComponent($plugin);

            foreach ($plugin->getComponents() as $component) {
                if ($component instanceof ComponentInterface) {
                    $componentManager->enableComponent($component);
                }
            }

            $enabledPlugins[$plugin->getId()] = get_class($plugin);

            $storageHelper->set(static::STORAGE_ENABLED_PLUGINS, $enabledPlugins);
            $storageHelper->save();

            return true;
        } catch (Exception $ex) {
            // Unable to enable plugin
        }

        return false;
    }

    /**
     * Disables plugin by id or instance.
     *
     * @param AbstractPluginComponent|AbstractPluginComponent[]|string $plugin
     * @return bool
     */
    public function disablePlugin($plugin)
    {
        if (is_array($plugin)) {
            foreach ($plugin as $item) {
                $this->disablePlugin($item);
            }

            return true;
        }

        $plugin = $this->findPlugin($plugin);

        if ($plugin === null) {
            return false;
        }

        try {
            /** @var StorageHelper $storageHelper */
            $storageHelper = $this->componizer->resolve(StorageHelper::class);

            $enabledPlugins = $storageHelper->get(static::STORAGE_ENABLED_PLUGINS, []);

            if (!isset($enabledPlugins[$plugin->getId()])) {
                return true;
            }

            /** @var ComponentManager $componentManager */
            $componentManager = $this->componizer->resolve(ComponentManager::class);

            $componentManager->disableComponent($plugin);

            foreach ($plugin->components() as $component) {
                if ($component instanceof ComponentInterface) {
                    $componentManager->disableComponent($component);
                }
            }

            unset($enabledPlugins[$plugin->getId()]);

            $storageHelper->set(static::STORAGE_ENABLED_PLUGINS, $enabledPlugins);
            $storageHelper->save();

            return true;
        } catch (Exception $ex) {
            // Unable to disable
        }

        return false;
    }

    /**
     * Tells if plugin is enabled by id or instance.
     *
     * @param AbstractPluginComponent|string $plugin
     * @return bool
     */
    public function isPluginEnabled($plugin)
    {
        $plugin = $this->findPlugin($plugin);

        if ($plugin === null) {
            return false;
        }

        $plugins = $this->getEnabledPlugins();

        return isset($plugins[$plugin->getId()]);
    }

    //-----------------------------------------------------
    // Private methods section
    //-----------------------------------------------------

    /**
     * Tells if plugin and all of its components is valid.
     *
     * @param AbstractPluginComponent|string $plugin
     * @return bool
     */
    private function isPluginValid($plugin)
    {
        if (!($plugin instanceof AbstractPluginComponent)) {
            return false;
        }

        /** @var ComponentManager $componentManager */
        $componentManager = $this->componizer->resolve(ComponentManager::class);

        if (!$componentManager->isComponentValid($plugin)) {
            return false;
        }

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
     * Initiates plugin and all of its components.
     *
     * @param AbstractPluginComponent $plugin
     */
    private function initPlugin(AbstractPluginComponent $plugin)
    {
        /** @var ComponentManager $componentManager */
        $componentManager = $this->componizer->resolve(ComponentManager::class);

        $componentManager->initComponent($plugin);

        foreach ($plugin->getComponents() as $component) {
            if ($component instanceof ComponentInterface) {
                $componentManager->initComponent($component);
            }
        }
    }

}