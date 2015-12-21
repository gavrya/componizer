<?php
/**
 * Created by PhpStorm.
 * User: gavrya
 * Date: 12/20/15
 * Time: 10:41 PM
 */

namespace Gavrya\Componizer\Skeleton;


/**
 * Interface ComponizerAsset required in order to implement componizer asset.
 *
 * @package Gavrya\Componizer\Skeleton
 */
interface ComponizerAsset
{

    // Include positions
    const POSITION_TOP = 'top';
    const POSITION_BOTTOM = 'bottom';

    /**
     * Returns asset include position.
     *
     * @see ComponizerAsset::POSITION_* constants
     *
     * @return string Include position value
     */
    public function position();

    /**
     * Returns HTML representation of the asset.
     *
     * @return string HTML representation of the asset
     */
    public function toHtml();

}