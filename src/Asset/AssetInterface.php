<?php
/**
 * Created by PhpStorm.
 * User: gavrya
 * Date: 12/20/15
 * Time: 10:41 PM
 */

namespace Gavrya\Componizer\Asset;


/**
 * Interface AssetInterface required in order to implement component related asset.
 *
 * @package Gavrya\Componizer\Asset
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
     * @return string Asset hash
     */
    public function getHash();

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
     * @param array $options
     * @return string HTML representation of the asset
     */
    public function toHtml(array $options = null);

}