<?php
/**
 * Created by PhpStorm.
 * User: gavrya
 * Date: 10/22/15
 * Time: 8:32 PM
 */

namespace Gavrya\Componizer;


use Closure;
use Gavrya\Componizer\Content\ContentParser;
use Gavrya\Componizer\Content\ContentProcessor;
use Gavrya\Componizer\Helper\DomHelper;
use Gavrya\Componizer\Helper\FsHelper;
use Gavrya\Componizer\Helper\StorageHelper;
use Gavrya\Componizer\Exception\ComponizerException;
use Gavrya\Componizer\Manager\ComponentManager;
use Gavrya\Componizer\Manager\PluginManager;
use Gavrya\Componizer\Manager\WidgetManager;


class Componizer
{

    // General constants
    const VERSION = '0.0.1';
    const PLUGIN_JSON_FILE_NAME = 'componizer.json';
    const CACHE_DIR_NAME = 'componizer';
    const PUBLIC_DIR_NAME = 'componizer';

    // Config keys
    const CONFIG_LANG = 'lang';
    const CONFIG_CACHE_DIR = 'cache_dir';
    const CONFIG_PUBLIC_DIR = 'public_dir';
    const CONFIG_PREVIEW_URL = 'preview_url';

    // Internal variables
    private static $config = null;
    private static $instance = null;
    private $container = [];

    //-----------------------------------------------------
    // Config setup/validation section
    //-----------------------------------------------------

    public static function setup($config)
    {
        if (self::$config !== null) {
            throw new ComponizerException('Unable to setup config multiple times', ComponizerException::EX_CONFIG_SETUP_ERROR);
        }

        $config = self::validateConfig($config);

        if (self::$config === null) {
            self::$config = $config;

            return true;
        }

        return false;
    }

    private static function validateConfig($config)
    {
        // check config type
        if (!is_array($config)) {
            throw new ComponizerException('Invalid config', ComponizerException::EX_CONFIG_SETUP_ERROR);
        }

        // config keys
        $keys = [
            self::CONFIG_LANG,
            self::CONFIG_CACHE_DIR,
            self::CONFIG_PUBLIC_DIR,
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
            throw new ComponizerException('Invalid lang', ComponizerException::EX_LANG_INVALID);
        }

        return $config;
    }

    private static function validateCacheDir($config)
    {
        // check if param provided
        if (!isset($config[self::CONFIG_CACHE_DIR])) {
            throw new ComponizerException('Invalid cache directory', ComponizerException::EX_CACHE_DIR_INVALID);
        }

        // cache dir path (must be an absolute not relative)
        $dirPath = $config[self::CONFIG_CACHE_DIR];

        // check dir exists
        if (!file_exists($dirPath)) {
            throw new ComponizerException('Cache directory is not exists: ' . $dirPath, ComponizerException::EX_CACHE_DIR_NOT_EXISTS);
        }

        // check dir
        if (is_link($dirPath) || !is_dir($dirPath)) {
            throw new ComponizerException('Cache directory is not a directory: ' . $dirPath,
                ComponizerException::EX_CACHE_DIR_NOT_DIRECTORY);
        }

        // check dir writable
        if (!is_writable($dirPath)) {
            throw new ComponizerException('Cache directory is not writable: ' . $dirPath,
                ComponizerException::EX_CACHE_DIR_NOT_WRITABLE);
        }

        // cache dir real path without trailing separator
        $dirPath = realpath($dirPath);

        // update config cache dir
        $config[self::CONFIG_CACHE_DIR] = $dirPath;

        // check cache dir name
        if (basename($dirPath) != self::CACHE_DIR_NAME) {
            // dedicated cache dir
            $dirPath = $dirPath . DIRECTORY_SEPARATOR . self::CACHE_DIR_NAME;

            // check dir exists and writable
            if (file_exists($dirPath) && is_dir($dirPath) && !is_writable($dirPath)) {
                throw new ComponizerException('Cache directory is not writable: ' . $dirPath,
                    ComponizerException::EX_CACHE_DIR_NOT_WRITABLE);
            }

            // make dir
            if ((!file_exists($dirPath) || !is_dir($dirPath)) && !mkdir($dirPath)) {
                throw new ComponizerException('Unable to create cache directory: ' . $dirPath,
                    ComponizerException::EX_CACHE_DIR_UNABLE_CREATE);
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
            throw new ComponizerException('Invalid public directory', ComponizerException::EX_PUBLIC_DIR_INVALID);
        }

        // public dir path (must be an absolute not relative)
        $dirPath = $config[self::CONFIG_PUBLIC_DIR];

        // check dir exists
        if (!file_exists($dirPath)) {
            throw new ComponizerException('Public directory is not exists: ' . $dirPath,
                ComponizerException::EX_PUBLIC_DIR_NOT_EXISTS);
        }

        // check dir
        if (is_link($dirPath) || !is_dir($dirPath)) {
            throw new ComponizerException('Public directory is not a directory: ' . $dirPath,
                ComponizerException::EX_PUBLIC_DIR_NOT_DIRECTORY);
        }

        // check writable
        if (!is_writable($dirPath)) {
            throw new ComponizerException('Public directory is not writable: ' . $dirPath,
                ComponizerException::EX_PUBLIC_DIR_NOT_WRITABLE);
        }

        // public dir real path without trailing separator
        $dirPath = realpath($dirPath);

        // update config public dir
        $config[self::CONFIG_PUBLIC_DIR] = $dirPath;

        // check public dir name
        if (basename($dirPath) != self::PUBLIC_DIR_NAME) {
            // dedicated public dir
            $dirPath = $dirPath . DIRECTORY_SEPARATOR . self::PUBLIC_DIR_NAME;

            // check dir exists and writable
            if (file_exists($dirPath) && is_dir($dirPath) && !is_writable($dirPath)) {
                throw new ComponizerException('Public directory is not writable: ' . $dirPath,
                    ComponizerException::EX_PUBLIC_DIR_NOT_WRITABLE);
            }

            // make dir
            if ((!file_exists($dirPath) || !is_dir($dirPath)) && !mkdir($dirPath)) {
                throw new ComponizerException('Unable to create public directory: ' . $dirPath,
                    ComponizerException::EX_PUBLIC_DIR_UNABLE_CREATE);
            }

            // update config public dir
            $config[self::CONFIG_PUBLIC_DIR] = $dirPath;
        }

        return $config;
    }

    private static function validatePreviewUrl($config)
    {
        // check if param provided
        if (!isset($config[self::CONFIG_PREVIEW_URL])) {
            throw new ComponizerException('Invalid preview url', ComponizerException::EX_PREVIEW_URL_INVALID);
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
        $this->initDependecyContainer();
        $this->removeBrokenSymlinks();
        // todo: remove unused cache dirs of removed components
    }

    private function removeBrokenSymlinks()
    {
        // public dir
        $publicDir = self::$config[self::CONFIG_PUBLIC_DIR];

        // FsHelper
        $fsHelper = $this->resolve(FsHelper::class);

        // remove broken symlinks
        $fsHelper->removeBrokenSymlinks($publicDir);
    }

    //-----------------------------------------------------
    // Dependency container section
    //-----------------------------------------------------

    private function initDependecyContainer()
    {
        // alias
        $componizer = $this;

        $this->container[FsHelper::class] = function () {
            return new FsHelper();
        };

        $this->container[StorageHelper::class] = function () use ($componizer) {
            return new StorageHelper($componizer->config()[Componizer::CONFIG_CACHE_DIR]);
        };

        $this->container[DomHelper::class] = function () {
            return new DomHelper();
        };

        $this->container[PluginManager::class] = function () use ($componizer) {
            return new PluginManager($componizer);
        };

        $this->container[ComponentManager::class] = function () use ($componizer) {
            return new ComponentManager($componizer);
        };

        $this->container[WidgetManager::class] = function () use ($componizer) {
            return new WidgetManager($componizer);
        };

        $this->container[ContentParser::class] = function () use ($componizer) {
            return new ContentParser($componizer);
        };

        $this->container[ContentProcessor::class] = function () use ($componizer) {
            return new ContentProcessor($componizer);
        };
    }

    public function resolve($class)
    {
        if (array_key_exists($class, $this->container)) {
            $dependency = $this->container[$class];
            return $dependency instanceof Closure ? $this->container[$class] = $dependency() : $dependency;
        }

        return null;
    }

    //-----------------------------------------------------
    // General methods section
    //-----------------------------------------------------

    /**
     * Get componizer config.
     *
     * @return array
     */
    public function config()
    {
        return self::$config;
    }

    /**
     * Get plugin manager.
     *
     * @return PluginManager Plugin manager
     */
    public function pluginManager()
    {
        return $this->resolve(PluginManager::class);
    }

    /**
     * Get widget manager.
     *
     * @return WidgetManager widget manager
     */
    public function widgetManager()
    {
        return $this->resolve(WidgetManager::class);
    }

    /**
     * Get content processor.
     *
     * @return ContentProcessor content processor
     */
    public function contentProcessor()
    {
        return $this->resolve(ContentProcessor::class);
    }

}