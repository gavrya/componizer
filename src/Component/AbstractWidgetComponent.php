<?php
/**
 * Created by PhpStorm.
 * User: gavrya
 * Date: 11/8/15
 * Time: 12:03 PM
 */

namespace Gavrya\Componizer\Component;

use Gavrya\Componizer\Asset\AssetsCollection;
use Gavrya\Componizer\Content\ContentParserInterface;


/**
 * Class AbstractWidgetComponent required in order to implement widget.
 *
 * @package Gavrya\Componizer\Component
 */
abstract class AbstractWidgetComponent implements ComponentInterface
{

    //-----------------------------------------------------
    // Assets methods section
    //-----------------------------------------------------

    /**
     * Returns editor related assets.
     *
     * @return AssetsCollection Assets collection
     */
    abstract public function getEditorAssets();

    /**
     * Returns display related assets.
     *
     * @return AssetsCollection Assets collection
     */
    abstract public function getDisplayAssets();

    //-----------------------------------------------------
    // Widget "display content" method section
    //-----------------------------------------------------

    /**
     * Makes widget "display content" HTML.
     *
     * @param ContentParserInterface $contentParser Content parser helper
     * @param array $properties Widget related JSON data
     * @param string $contentType Content type of the passed content
     * @param string|null $content Widget content in format of "editor content"
     * @return string Generated "display content" HTML
     */
    public function makeDisplayContent(
        ContentParserInterface $contentParser,
        array $properties,
        $contentType,
        $content = null
    ) {
        return '';
    }

}