<?php
/**
 * Created by PhpStorm.
 * User: gavrya
 * Date: 10/26/15
 * Time: 4:54 PM
 */

namespace Gavrya\Gravitizer;


use Exception;
use Gavrya\Gravitizer\Helper\FsHelper;
use Gavrya\Gravitizer\Helper\StorageHelper;
use Gavrya\Gravitizer\Skeleton\GravitizerComponent;
use Gavrya\Gravitizer\Skeleton\GravitizerException;
use Gavrya\Gravitizer\Skeleton\GravitizerPlugin;
use Gavrya\Gravitizer\Skeleton\GravitizerPluginManager;

class PluginManager implements GravitizerPluginManager
{

    // Gravitizer
    private $gravitizer = null;

    // Plugins
    private $plugins = null;

    //-----------------------------------------------------
    // Instance creation/init section
    //-----------------------------------------------------

    public function __construct(Gravitizer $gravitizer)
    {
        $this->gravitizer = $gravitizer;
    }

    //-----------------------------------------------------
    // GravitizerPluginManager implementation section
    //-----------------------------------------------------

    public function all()
    {
        if ($this->plugins !== null) {
            return $this->plugins;
        }

        $plugins = [];
        $fsHelper = $this->gravitizer->resolve(FsHelper::class);

        if ($fsHelper instanceof FsHelper) {
            // prepare data
            $vendorPath = $fsHelper->composerVendorDir();
            $jsonFiles = $fsHelper->pluginsJsonFiles($vendorPath, Gravitizer::PLUGIN_JSON_FILE_NAME);
            $jsonData = $fsHelper->pluginsJsonData($jsonFiles);

            // check plugin data
            foreach ($jsonData as $data) {
                // check version
                if (!isset($data['gravitizer_version']) || $data['gravitizer_version'] !== Gravitizer::VERSION) {
                    // ignore plugin with wrong version
                    continue;
                }

                // load plugin by full class name
                if (isset($data['plugin_class'])) {
                    // this action may throw FatalException if class does not exists or it cant be loaded by any autoloader
                    $plugin = new $data['plugin_class'];

                    // check instance
                    if ($plugin instanceof GravitizerComponent && $plugin instanceof GravitizerPlugin) {
                        // gravitizer config
                        $config = $this->gravitizer->config();

                        // lang
                        $lang = $config[Gravitizer::CONFIG_LANG];

                        // create plugin cache dir
                        $pluginCacheDir = $config[Gravitizer::CONFIG_CACHE_DIR] . DIRECTORY_SEPARATOR . $plugin->id();
                        if(!is_dir($pluginCacheDir)) {
                            mkdir($pluginCacheDir);
                        }

                        // init plugin
                        $plugin->init($lang);

                        // init plugin components
                        /*
                        if($plugin->hasWidgets()) {
                            foreach($plugin->widgets() as $widget) {
                                if($widget instanceof GravitizerComponent && $widget instanceof Widget) {
                                    $widget->init($lang);
                                }
                            }
                        }
                        */

                        // add plugin to plugin list
                        // check plugin already exists, throw exception ?
                        $plugins[$plugin->id()] = $plugin;
                    }
                }
            }
        }

        return $this->plugins = $plugins;
    }

    public function get($plugin)
    {
        $pluginId = null;
        if ($plugin instanceof GravitizerComponent && $plugin instanceof GravitizerPlugin) {
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
        $storageHelper = $this->gravitizer->resolve(StorageHelper::class);
        if ($storageHelper instanceof StorageHelper) {
            $plugins = $storageHelper->get('enabled_plugins', []);

            return array_intersect_key($this->all(), $plugins);
        }
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
            $storageHelper = $this->gravitizer->resolve(StorageHelper::class);
            if ($storageHelper instanceof StorageHelper) {
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
            }
        } catch (Exception $ex) {
            throw new GravitizerException('Unable to enable plugin with id: ' . $plugin->id());
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
            $storageHelper = $this->gravitizer->resolve(StorageHelper::class);
            if ($storageHelper instanceof StorageHelper) {
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
            }
        } catch (Exception $ex) {
            throw new GravitizerException('Unable to disable plugin with id: ' . $plugin->id());
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