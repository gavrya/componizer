<?php
/**
 * Created by PhpStorm.
 * User: gavrya
 * Date: 10/22/15
 * Time: 8:32 PM
 */

namespace Gavrya\Componizer;


use Closure;
use Gavrya\Componizer\Helpers\DomHelper;
use Gavrya\Componizer\Helpers\FsHelper;
use Gavrya\Componizer\Helpers\StorageHelper;
use Gavrya\Componizer\Managers\ComponentManager;
use Gavrya\Componizer\Managers\PluginManager;
use Gavrya\Componizer\Managers\WidgetManager;
use Gavrya\Componizer\Processing\ContentParser;
use Gavrya\Componizer\Processing\ContentProcessor;
use Gavrya\Componizer\Processing\WidgetParser;
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

    /**
     * @var ComponizerConfig
     */
    private $config = null;

    /**
     * @var array
     */
    private $dependencies = [];

    //-----------------------------------------------------
    // Constructor section
    //-----------------------------------------------------

    /**
     * Componizer constructor.
     *
     * @param ComponizerConfig $config
     *
     * @throws InvalidArgumentException
     */
    public function __construct(ComponizerConfig $config)
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
     * @return ComponizerConfig
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * Returns plugin manager.
     *
     * @return PluginManager
     */
    public function getPluginManager()
    {
        return $this->resolve(PluginManager::class);
    }

    /**
     * Returns widget manager.
     *
     * @return WidgetManager
     */
    public function getWidgetManager()
    {
        return $this->resolve(WidgetManager::class);
    }

    /**
     * Returns content processor.
     *
     * @return ContentProcessor
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
     * @param string $className
     * @return mixed|null
     */
    public function resolve($className)
    {
        if (array_key_exists($className, $this->dependencies)) {
            $dependency = $this->dependencies[$className];

            return $dependency instanceof Closure ? $this->dependencies[$className] = $dependency() : $dependency;
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

        $this->dependencies[FsHelper::class] = function () {
            return new FsHelper();
        };

        $this->dependencies[StorageHelper::class] = function () use ($componizer) {
            return new StorageHelper($componizer->getConfig()->get(ComponizerConfig::CONFIG_CACHE_DIR));
        };

        $this->dependencies[DomHelper::class] = function () {
            return new DomHelper();
        };

        $this->dependencies[PluginManager::class] = function () use ($componizer) {
            return new PluginManager($componizer);
        };

        $this->dependencies[ComponentManager::class] = function () use ($componizer) {
            return new ComponentManager($componizer);
        };

        $this->dependencies[WidgetManager::class] = function () use ($componizer) {
            return new WidgetManager($componizer);
        };

        $this->dependencies[WidgetParser::class] = function () use ($componizer) {
            return new WidgetParser($componizer);
        };

        $this->dependencies[ContentParser::class] = function () use ($componizer) {
            return new ContentParser($componizer);
        };

        $this->dependencies[ContentProcessor::class] = function () use ($componizer) {
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
        $this->prepareConfigDirs();
        $this->removeBrokenSymlinks();
        // todo: remove unused cache dirs of removed components
    }

    /**
     * Removes broken symlinks.
     */
    private function removeBrokenSymlinks()
    {
        $publicDir = $this->config->get(ComponizerConfig::CONFIG_PUBLIC_DIR);

        /** @var FsHelper $fsHelper */
        $fsHelper = $this->resolve(FsHelper::class);

        $fsHelper->removeBrokenSymlinks($publicDir);
    }

    /**
     * Creates config dirs if not exits.
     */
    private function prepareConfigDirs()
    {
        $publicDir = $this->config->get(ComponizerConfig::CONFIG_PUBLIC_DIR);
        $cacheDir = $this->config->get(ComponizerConfig::CONFIG_CACHE_DIR);

        /** @var FsHelper $fsHelper */
        $fsHelper = $this->resolve(FsHelper::class);

        $fsHelper->makeDir($publicDir);
        $fsHelper->makeDir($cacheDir);
    }

}