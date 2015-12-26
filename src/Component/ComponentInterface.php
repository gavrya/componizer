<?php
/**
 * Created by PhpStorm.
 * User: gavrya
 * Date: 10/25/15
 * Time: 2:49 PM
 */

namespace Gavrya\Componizer\Component;

/**
 * Interface required in order to implement any componizer component.
 *
 * @package Gavrya\Componizer\Component
 */
interface ComponentInterface
{

    /**
     * Returns unique component id.
     *
     * Must be globally unique, lowercased string with the first 8 characters from a random sha-1 hash.
     * Must be the same for all versions of the component.
     *
     * @return string Component id
     */
    public function getId();

    /**
     * Returns component name.
     *
     * @return string Component name
     */
    public function getName();

    /**
     * Returns component version.
     *
     * @return string Component version
     */
    public function getVersion();

    /**
     * Returns short information about the component without containing HTML tags.
     *
     * @return string Component information
     */
    public function getInfo();

    /**
     * Tells if the component is shipped with assets that need to be symlinked to the public directory.
     *
     * @return bool true if component has assets that need to be symlinked to the public directory, false otherwise
     */
    public function hasAssets();

    /**
     * Returns absolute path to the component assets directory.
     *
     * @return string|null Absolute path to the component assets directory, null if component has no assets
     */
    public function getAssetsDir();

    /**
     * Callback method that is invoked when the component need to be initialed.
     *
     * @param string $lang Language code
     * @param string $cacheDir Absolute path to the component cache directory where component data storing allowed
     */
    public function init($lang, $cacheDir);

    /**
     * Callback method that is invoked when the component has been enabled (aka installed).
     */
    public function up();

    /**
     * Callback method that is invoked when the component has been disabled (aka uninstalled).
     */
    public function down();

}