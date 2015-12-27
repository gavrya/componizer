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


class Componizer
{

    // General constants
    const VERSION = '0.0.1';
    const PLUGIN_JSON_FILE_NAME = 'componizer.json';
    const CACHE_DIR_NAME = 'componizer';
    const PUBLIC_DIR_NAME = 'componizer';

    /**
     * @var ComponizerConfig|null
     */
    private $config = null;

    /**
     * @var array
     */
    private $container = [];

    //-----------------------------------------------------
    // Construct section
    //-----------------------------------------------------

    private function __construct(ComponizerConfig $config)
    {
        if($config === null || !$config->isValid()) {
            throw new InvalidArgumentException('Invalid config');
        }

        $this->config = $config;

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
        $publicDir = $this->config->get(ComponizerConfig::CONFIG_PUBLIC_DIR);

        $fsHelper = $this->resolve(FsHelper::class);

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
            return new StorageHelper($componizer->getConfig()->get(ComponizerConfig::CONFIG_CACHE_DIR));
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
     * @return ComponizerConfig|null
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * Get plugin manager.
     *
     * @return PluginManager Plugin manager
     */
    public function getPluginManager()
    {
        return $this->resolve(PluginManager::class);
    }

    /**
     * Get widget manager.
     *
     * @return WidgetManager widget manager
     */
    public function getWidgetManager()
    {
        return $this->resolve(WidgetManager::class);
    }

    /**
     * Get content processor.
     *
     * @return ContentProcessor content processor
     */
    public function getContentProcessor()
    {
        return $this->resolve(ContentProcessor::class);
    }

}