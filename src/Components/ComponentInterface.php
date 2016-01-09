<?php
/**
 * Created by PhpStorm.
 * User: gavrya
 * Date: 10/25/15
 * Time: 2:49 PM
 */

namespace Gavrya\Componizer\Components;

/**
 * Interface ComponentInterface required in order to implement any componizer component.
 *
 * @package Gavrya\Componizer\Components
 */
interface ComponentInterface
{

    /**
     * Returns unique component id.
     *
     * Must be globally unique, lowercased string with the first 8 characters from a random sha-1 hash.
     * Must be the same for all versions of the component.
     *
     * @return string
     */
    public function getId();

    /**
     * Returns component name.
     *
     * @return string
     */
    public function getName();

    /**
     * Returns component version.
     *
     * @return string
     */
    public function getVersion();

    /**
     * Returns short information about the component without containing HTML tags.
     *
     * @return string
     */
    public function getInfo();

    /**
     * Tells if the component is shipped with assets that need to be symlinked to the public directory.
     *
     * @return bool
     */
    public function hasAssets();

    /**
     * Returns absolute path to the component assets directory.
     *
     * @return string|null
     */
    public function getAssetsDir();

    /**
     * Callback method that is invoked when the component need to be initialed.
     *
     * @param string $lang
     * @param string $cacheDir
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