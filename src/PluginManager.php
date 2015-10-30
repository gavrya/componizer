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

    }

    //-----------------------------------------------------
    // Helpers section
    //-----------------------------------------------------

    private function initPlugin($plugin)
    {
        if ($plugin instanceof ComponizerComponent && $plugin instanceof ComponizerPlugin) {
            // gravitizer config
            $config = $this->componizer->config();

            // lang
            $lang = $config[Componizer::CONFIG_LANG];

            // create plugin cache dir
            $pluginCacheDir = $config[Componizer::CONFIG_CACHE_DIR] . DIRECTORY_SEPARATOR . $plugin->id();
            if (!is_dir($pluginCacheDir)) {
                mkdir($pluginCacheDir);
            }

            // TODO: do all plugin related checks: cache/public dirs exists (create if not) ...

            // init plugin
            $plugin->init($lang);

            // init plugin components
            /*
            if($plugin->hasWidgets()) {
                foreach($plugin->widgets() as $widget) {
                    if($widget instanceof ComponizerComponent && $widget instanceof Widget) {
                        $widget->init($lang);
                    }
                }
            }
            */

            return true;
        }

        return false;
    }

    private function initComponent(ComponizerComponent $component)
    {
        // TODO: do all component related checks: cache/public dirs exists (create if not) ...

        // check and sync assets dir

        // call init() method
    }

    private function enableComponent(ComponizerComponent $component)
    {
        // sync assets dir

        // call up() method
    }

    private function disableComponent(ComponizerComponent $component)
    {
        // unsync assets dir

        // call down() method
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
            if (!isset($data['componizer_version']) || $data['componizer_version'] !== Componizer::VERSION) {
                continue;
            }

            // check plugin class
            if (!isset($data['plugin_class'])) {
                continue;
            }

            // create plugin instance from class name
            // this action may throw FatalException if class does not exists or it cant be loaded by any autoloader
            $plugin = new $data['plugin_class'];

            // check plugin instance
            if (!($plugin instanceof ComponizerComponent && $plugin instanceof ComponizerPlugin)) {
                continue;
            }

            // check plugin id
            $pluginId = $plugin->id();

            // check plugin id is lowercase md5
            if (!preg_match('/^[a-f0-9]{32}$/', $pluginId)) {
                continue;
            }

            // init plugin and add to available plugins
            if ($this->initPlugin($plugin)) {
                $plugins[$pluginId] = $plugin;
            }
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
        // check plugin
        $plugin = $this->get($plugin);
        if ($plugin === null) {
            return false;
        }

        // save to storage
        try {
            $storageHelper = $this->componizer->resolve(StorageHelper::class);
            $plugins = $storageHelper->get('enabled_plugins', []);

            if (!isset($plugins[$plugin->id()])) {
                // TODO: copy/symlink assets dir to public dir

                // call up() method
                $plugin->up();

                // enable plugin components

                // update enabled plugins
                $plugins[$plugin->id()] = get_class($plugin);

                // update storage
                $storageHelper->set('enabled_plugins', $plugins);
                $storageHelper->save();

                return true;
            }
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

            // check if exists
            if (isset($plugins[$plugin->id()])) {
                // call down() method
                $plugin->down();

                // TODO: delete assets dir/symlink from public dir

                // disable plugin components

                // update enabled plugins
                unset($plugins[$plugin->id()]);

                // update storage
                $storageHelper->set('enabled_plugins', $plugins);
                $storageHelper->save();

                return true;
            }
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
}