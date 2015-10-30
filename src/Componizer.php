<?php
/**
 * Created by PhpStorm.
 * User: gavrya
 * Date: 10/22/15
 * Time: 8:32 PM
 */

namespace Gavrya\Componizer;


use Closure;
use Gavrya\Componizer\Helper\FsHelper;
use Gavrya\Componizer\Helper\StorageHelper;
use Gavrya\Componizer\Skeleton\ComponizerInstance;
use Gavrya\Componizer\Skeleton\ComponizerException;

class Componizer implements ComponizerInstance
{

    // Config keys
    const VERSION = '0.0.1';
    const PLUGIN_JSON_FILE_NAME = 'componizer.json';

    // Config keys
    const CONFIG_LANG = 'lang';
    const CONFIG_CACHE_DIR = 'cache_dir';
    const CONFIG_PUBLIC_DIR = 'public_dir';
    const CONFIG_ASSETS_HANDLER = 'assets_handler';
    const CONFIG_PREVIEW_URL = 'preview_url';

    // Directory names
    const CACHE_DIR_NAME = 'gravitizer';
    const PUBLIC_DIR_NAME = 'gravitizer';

    // Assets handlers
    const ASSETS_HANDLER_COPY_BY_PHP = 'copy_by_php';
    const ASSETS_HANDLER_COPY_BY_SHELL = 'copy_by_shell';
    const ASSETS_HANDLER_SYMLINK_BY_PHP = 'symlink_by_php';
    const ASSETS_HANDLER_SYMLINK_BY_SHELL = 'symlink_by_shell';

    // Exception codes
    const EX_ERROR = 0;
    // ---
    const EX_CONFIG_SETUP_ERROR = 100;
    // ---
    const EX_LANG_INVALID = 200;
    // ---
    const EX_CACHE_DIR_INVALID = 300;
    const EX_CACHE_DIR_NOT_EXISTS = 310;
    const EX_CACHE_DIR_NOT_DIRECTORY = 320;
    const EX_CACHE_DIR_NOT_WRITABLE = 330;
    const EX_CACHE_DIR_UNABLE_CREATE = 340;
    // ---
    const EX_PUBLIC_DIR_INVALID = 400;
    const EX_PUBLIC_DIR_NOT_EXISTS = 410;
    const EX_PUBLIC_DIR_NOT_DIRECTORY = 420;
    const EX_PUBLIC_DIR_NOT_WRITABLE = 430;
    const EX_PUBLIC_DIR_UNABLE_CREATE = 440;
    // ---
    const EX_ASSETS_HANDLER_INVALID = 500;
    // ---
    const EX_PREVIEW_URL_INVALID = 600;

    // Class variables
    private static $config = null;
    private static $instance = null;
    private $container = [];

    //-----------------------------------------------------
    // Config setup/validation section
    //-----------------------------------------------------

    public static function setup($config)
    {
        if (self::$config !== null) {
            throw new ComponizerException('Unable to setup config multiple times', self::EX_CONFIG_SETUP_ERROR);
        }

        $config = self::validateConfig($config);
        if (self::$config === null) {
            self::$config = $config;
        }
    }

    private static function validateConfig($config)
    {
        // check config type
        if (!is_array($config)) {
            throw new ComponizerException('Invalid config', self::EX_CONFIG_SETUP_ERROR);
        }

        // config keys
        $keys = [
            self::CONFIG_LANG,
            self::CONFIG_CACHE_DIR,
            self::CONFIG_PUBLIC_DIR,
            self::CONFIG_ASSETS_HANDLER,
            self::CONFIG_PREVIEW_URL
        ];

        // leave only config related keys and values
        $config = array_intersect_key($config, array_flip($keys));

        // validate lang
        $config = self::validateLang($config);

        // validate cache dir
        $config = self::validateCacheDir($config);

        // validate public dir
        $config = self::validatePublicDir($config);

        // validate assets handler
        $config = self::validateAssetsHandler($config);

        // validate preview url
        $config = self::validatePreviewUrl($config);

        return $config;
    }

    private static function validateLang($config)
    {
        // check if param provided
        if (!isset($config[self::CONFIG_LANG])) {
            // set default lang
            $config[self::CONFIG_LANG] = 'en';
        }

        // check/modify lang
        if (!in_array($config[self::CONFIG_LANG], ['en', 'ru'])) {
            throw new ComponizerException('Invalid lang', self::EX_LANG_INVALID);
        }

        return $config;
    }

    private static function validateCacheDir($config)
    {
        // check if param provided
        if (!isset($config[self::CONFIG_CACHE_DIR])) {
            throw new ComponizerException('Invalid cache directory', self::EX_CACHE_DIR_INVALID);
        }

        // cache dir path (must be an absolute not relative)
        $dirPath = $config[self::CONFIG_CACHE_DIR];

        // check dir exists
        if (!file_exists($dirPath)) {
            throw new ComponizerException('Cache directory is not exists: ' . $dirPath, self::EX_CACHE_DIR_NOT_EXISTS);
        }

        // check dir
        if (!is_dir($dirPath)) {
            throw new ComponizerException('Cache directory is not a directory: ' . $dirPath,
                self::EX_CACHE_DIR_NOT_DIRECTORY);
        }

        // check dir writable
        if (!is_writable($dirPath)) {
            throw new ComponizerException('Cache directory is not writable: ' . $dirPath,
                self::EX_CACHE_DIR_NOT_WRITABLE);
        }

        // cache dir real path without trailing separator
        $dirPath = realpath($dirPath);

        // update config cache dir
        $config[self::CONFIG_CACHE_DIR] = $dirPath;

        // check cache dir name
        if (basename($dirPath) != self::CACHE_DIR_NAME) {
            // create dedicated cache dir if needed
            $dirPath = $dirPath . DIRECTORY_SEPARATOR . self::CACHE_DIR_NAME;

            // check dir exists and writable
            if (is_dir($dirPath) && !is_writable($dirPath)) {
                throw new ComponizerException('Cache directory is not writable: ' . $dirPath,
                    self::EX_CACHE_DIR_NOT_WRITABLE);
            }

            // make dir
            if (!is_dir($dirPath) && !mkdir($dirPath)) {
                throw new ComponizerException('Unable to create cache directory: ' . $dirPath,
                    self::EX_CACHE_DIR_UNABLE_CREATE);
            }

            // update config cache dir
            $config[self::CONFIG_CACHE_DIR] = $dirPath;
        }

        return $config;
    }

    private static function validatePublicDir($config)
    {
        // check if param provided
        if (!isset($config[self::CONFIG_PUBLIC_DIR])) {
            throw new ComponizerException('Invalid public directory', self::EX_PUBLIC_DIR_INVALID);
        }

        // public dir path (must be an absolute not relative)
        $dirPath = $config[self::CONFIG_PUBLIC_DIR];

        // check dir exists
        if (!file_exists($dirPath)) {
            throw new ComponizerException('Public directory is not exists: ' . $dirPath,
                self::EX_PUBLIC_DIR_NOT_EXISTS);
        }

        // check dir
        if (!is_dir($dirPath)) {
            throw new ComponizerException('Public directory is not a directory: ' . $dirPath,
                self::EX_PUBLIC_DIR_NOT_DIRECTORY);
        }

        // check writable
        if (!is_writable($dirPath)) {
            throw new ComponizerException('Public directory is not writable: ' . $dirPath,
                self::EX_PUBLIC_DIR_NOT_WRITABLE);
        }

        // public dir real path without trailing separator
        $dirPath = realpath($dirPath);

        // update config public dir
        $config[self::CONFIG_PUBLIC_DIR] = $dirPath;

        // check public dir name
        if (basename($dirPath) != self::PUBLIC_DIR_NAME) {
            // create dedicated public dir if needed
            $dirPath = $dirPath . DIRECTORY_SEPARATOR . self::PUBLIC_DIR_NAME;

            // check dir exists and writable
            if (is_dir($dirPath) && !is_writable($dirPath)) {
                throw new ComponizerException('Public directory is not writable: ' . $dirPath,
                    self::EX_PUBLIC_DIR_NOT_WRITABLE);
            }

            // make dir
            if (!is_dir($dirPath) && !mkdir($dirPath)) {
                throw new ComponizerException('Unable to create public directory: ' . $dirPath,
                    self::EX_PUBLIC_DIR_UNABLE_CREATE);
            }

            // update config public dir
            $config[self::CONFIG_PUBLIC_DIR] = $dirPath;
        }

        return $config;
    }

    private static function validateAssetsHandler($config)
    {
        // assets handlers
        $handlers = [
            self::ASSETS_HANDLER_COPY_BY_PHP,
            self::ASSETS_HANDLER_COPY_BY_SHELL,
            self::ASSETS_HANDLER_SYMLINK_BY_PHP,
            self::ASSETS_HANDLER_SYMLINK_BY_SHELL,
        ];

        // check if param provided
        if (!isset($config[self::CONFIG_ASSETS_HANDLER])) {
            // set default assets handler
            $config[self::CONFIG_ASSETS_HANDLER] = self::ASSETS_HANDLER_SYMLINK_BY_PHP;
        }

        // check param value
        if (!in_array($config[self::CONFIG_ASSETS_HANDLER], $handlers)) {
            throw new ComponizerException('Invalid assets handler', self::EX_ASSETS_HANDLER_INVALID);
        }

        return $config;
    }

    private static function validatePreviewUrl($config)
    {
        // check if param provided
        if (!isset($config[self::CONFIG_PREVIEW_URL])) {
            throw new ComponizerException('Invalid preview url', self::EX_PREVIEW_URL_INVALID);
        }

        return $config;
    }

    //-----------------------------------------------------
    // Instance creation/init section
    //-----------------------------------------------------

    public static function instance()
    {
        if (self::$config === null) {
            throw new ComponizerException('Unable to create instance without config');
        }

        return self::$instance !== null ? self::$instance : self::$instance = new self();
    }

    private function __construct()
    {
        $this->init();
    }

    private function init()
    {
        // alias
        $componizer = $this;

        // Helpers init
        $this->container[FsHelper::class] = function () {
            return new FsHelper();
        };

        $this->container[StorageHelper::class] = function () use ($componizer) {
            $config = $componizer->config();

            return new StorageHelper($config[Componizer::CONFIG_CACHE_DIR]);
        };

        // Component Managers init
        $this->container[PluginManager::class] = function () use ($componizer) {
            return new PluginManager($componizer);
        };
    }

    //-----------------------------------------------------
    // Dependency resolver section
    //-----------------------------------------------------

    public function resolve($class)
    {
        if (array_key_exists($class, $this->container)) {
            $target = $this->container[$class];
            if ($target instanceof Closure) {
                return $this->container[$class] = $target();
            } else {
                return $this->container[$class];
            }
        }

        throw new ComponizerException('Unable to resolve class: ' . $class);
    }

    //-----------------------------------------------------
    // ComponizerInstance implementation section
    //-----------------------------------------------------

    public function version()
    {
        return self::VERSION;
    }

    public function config()
    {
        return self::$config;
    }

    public function pluginManager()
    {
        $this->resolve(PluginManager::class);
    }

    public function widgetManager()
    {
        //$this->resolve(WidgetManager::class);
    }

}