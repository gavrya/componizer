<?php
/**
 * Created by PhpStorm.
 * User: gavrya
 * Date: 12/20/15
 * Time: 10:41 PM
 */

namespace Gavrya\Componizer\Asset;


/**
 * Interface AssetInterface required in order to implement componizer asset.
 *
 * @package Gavrya\Componizer\AssetInterface
 */
interface AssetInterface
{

    // Include positions
    const POSITION_HEAD = 'head';
    const POSITION_BODY_TOP = 'body_top';
    const POSITION_BODY_BOTTOM = 'body_bottom';

    /**
     * Returns asset include position.
     *
     * @see ComponizerAsset::POSITION_* constants
     *
     * @return string Include position value
     */
    public function getPosition();

    /**
     * Returns HTML representation of the asset.
     *
     * @return string HTML representation of the asset
     */
    public function toHtml();

}