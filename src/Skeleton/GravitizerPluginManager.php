<?php
/**
 * Created by PhpStorm.
 * User: gavrya
 * Date: 10/22/15
 * Time: 10:22 AM
 */

namespace Gavrya\Gravitizer\Skeleton;


interface GravitizerPluginManager
{

    //-----------------------------------------------------
    // Get section
    //-----------------------------------------------------

    /**
     * Return array of all available plugins as $pluginId => $plugin.
     *
     * @return mixed
     */
    public function all();

    /**
     * Return plugin by id.
     *
     * @param $plugin
     * @return mixed
     */
    public function get($plugin);

    //-----------------------------------------------------
    // Enable/Disable section
    //-----------------------------------------------------

    /**
     * Return all enabled plugins as $pluginId => $plugin.
     *
     * @return mixed
     */
    public function enabled();

    /**
     * Return all disabled plugins as $pluginId => $plugin.
     *
     * @return mixed
     */
    public function disabled();

    /**
     * Enable plugin by plugin or id.
     * Enable plugins by array of plugins or ids.
     *
     * @param $plugin
     * @return mixed
     */
    public function enable($plugin);

    /**
     * Disable plugin by plugin or id.
     * Disable plugins by array of plugins or ids.
     *
     * @param $plugin
     * @return mixed
     */
    public function disable($plugin);

    /**
     * Check if the plugin is enabled by plugin or id.
     *
     * @return mixed
     */
    public function isEnabled($plugin);

    //-----------------------------------------------------
    // Allow/Deny section
    //-----------------------------------------------------

    // add scopes

    //public function allowed();
    //public function denied();
    //public function allowAll();
    //public function allowOnly($plugin);
    //public function denyOnly($plugin);
    //public function isAllowed($plugin);

}