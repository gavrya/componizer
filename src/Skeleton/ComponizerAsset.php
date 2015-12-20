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
 * @see \Gavrya\Componizer\Skeleton\ComponizerExternalJs
 * @see \Gavrya\Componizer\Skeleton\ComponizerInternalJs
 * @see \Gavrya\Componizer\Skeleton\ComponizerExternalCss
 * @see \Gavrya\Componizer\Skeleton\ComponizerInternalCss
 *
 * @package Gavrya\Componizer\Skeleton
 */
interface ComponizerAsset
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
    public function position();

    /**
     * Returns HTML representation of the asset.
     *
     * @param mixed $params Optional parameters
     * @return string HTML representation of the asset
     */
    public function toHtml($params = null);

}