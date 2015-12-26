<?php
/**
 * Created by PhpStorm.
 * User: gavrya
 * Date: 12/20/15
 * Time: 10:41 PM
 */

namespace Gavrya\Componizer\Asset;


/**
 * Interface ComponizerAsset required in order to implement componizer asset.
 *
 * @package Gavrya\Componizer\Asset
 */
interface ComponizerAsset
{

    // Include positions
    const TYPE_EXTERNAL_JS = 'external_js';
    const TYPE_INTERNAL_JS = 'internal_js';
    const TYPE_EXTERNAL_CSS = 'external_css';
    const TYPE_INTERNAL_CSS = 'internal_css';

    // Include positions
    const POSITION_HEAD = 'head';
    const POSITION_BODY_TOP = 'body_top';
    const POSITION_BODY_BOTTOM = 'body_bottom';

    /**
     * Returns type of the asset.
     *
     * @see ComponizerAsset::TYPE_* constants
     *
     * @return string Asset type
     */
    public function getType();

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