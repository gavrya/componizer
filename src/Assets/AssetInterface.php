<?php
/**
 * Created by PhpStorm.
 * User: gavrya
 * Date: 12/20/15
 * Time: 10:41 PM
 */

namespace Gavrya\Componizer\Assets;


/**
 * Interface AssetInterface required in order to implement component related asset.
 *
 * @package Gavrya\Componizer\Assets
 */
interface AssetInterface
{

    // Include positions
    const POSITION_HEAD = 'head';
    const POSITION_BODY_TOP = 'body_top';
    const POSITION_BODY_BOTTOM = 'body_bottom';

    /**
     * Returns asset unique hash.
     *
     * Two objects with the same hashes are considered equal.
     *
     * @return string
     */
    public function getHash();

    /**
     * Returns asset include position.
     *
     * @see ComponizerAsset::POSITION_* constants
     *
     * @return string
     */
    public function getPosition();

    /**
     * Returns HTML representation of the asset.
     *
     * @return string
     */
    public function toHtml();

}