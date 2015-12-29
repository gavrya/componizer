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
use Gavrya\Componizer\Manager\ComponentManager;
use Gavrya\Componizer\Manager\PluginManager;
use Gavrya\Componizer\Manager\WidgetManager;
use InvalidArgumentException;

/**
 * Class Componizer represents the general class to interact with the library.
 *
 * @package Gavrya\Componizer
 */
class Componizer
{

    // General constants
    const VERSION = '0.0.1';
    const PLUGIN_JSON_FILE_NAME = 'componizer.json';
    const CACHE_DIR_NAME = 'componizer';
    const PUBLIC_DIR_NAME = 'componizer';

    /**
     * @var Config Configuration
     */
    private $config = null;

    /**
     * @var array Dependency container
     */
    private $container = [];

    //-----------------------------------------------------
    // Constructor section
    //-----------------------------------------------------

    /**
     * Componizer constructor.
     *
     * @param Config $config Configuration
     *
     * @throws InvalidArgumentException When invalid configuration passed
     */
    private function __construct(Config $config)
    {
        if($config === null || !$config->isValid()) {
            throw new InvalidArgumentException('Invalid config');
        }

        $this->config = $config;
        $this->init();
    }

    //-----------------------------------------------------
    // General methods section
    //-----------------------------------------------------

    /**
     * @return Config Configuration
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * Returns plugin manager.
     *
     * @return PluginManager Plugin manager
     */
    public function getPluginManager()
    {
        return $this->resolve(PluginManager::class);
    }

    /**
     * Returns widget manager.
     *
     * @return WidgetManager Widget manager
     */
    public function getWidgetManager()
    {
        return $this->resolve(WidgetManager::class);
    }

    /**
     * Returns content processor.
     *
     * @return ContentProcessor Content processor
     */
    public function getContentProcessor()
    {
        return $this->resolve(ContentProcessor::class);
    }

    //-----------------------------------------------------
    // Dependency container methods section
    //-----------------------------------------------------

    /**
     * Returns resolved dependency by the class name.
     *
     * @param string $className Class name
     * @return mixed|null Resolved object, null otherwise
     */
    public function resolve($className)
    {
        if (array_key_exists($className, $this->container)) {
            $dependency = $this->container[$className];

            return $dependency instanceof Closure ? $this->container[$className] = $dependency() : $dependency;
        }

        return null;
    }

    /**
     * Initiates dependency container.
     */
    private function initDependecyContainer()
    {
        // alias
        $componizer = $this;

        $this->container[FsHelper::class] = function () {
            return new FsHelper();
        };

        $this->container[StorageHelper::class] = function () use ($componizer) {
            return new StorageHelper($componizer->getConfig()->get(Config::CONFIG_CACHE_DIR));
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

    //-----------------------------------------------------
    // Init methods section
    //-----------------------------------------------------

    /**
     * Initiates object.
     */
    private function init()
    {
        $this->initDependecyContainer();
        $this->removeBrokenSymlinks();
        // todo: remove unused cache dirs of removed components
    }

    /**
     * Removes broken symlinks.
     */
    private function removeBrokenSymlinks()
    {
        $publicDir = $this->config->get(Config::CONFIG_PUBLIC_DIR);

        /** @var FsHelper $fsHelper */
        $fsHelper = $this->resolve(FsHelper::class);

        $fsHelper->removeBrokenSymlinks($publicDir);
    }

}