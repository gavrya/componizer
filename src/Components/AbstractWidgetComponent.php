<?php
/**
 * Created by PhpStorm.
 * User: gavrya
 * Date: 11/8/15
 * Time: 12:03 PM
 */

namespace Gavrya\Componizer\Components;

use Gavrya\Componizer\Assets\AssetsCollection;
use Gavrya\Componizer\Processing\ContentParserInterface;


/**
 * Class AbstractWidgetComponent required in order to implement widget.
 *
 * @package Gavrya\Componizer\Components
 */
abstract class AbstractWidgetComponent implements ComponentInterface
{

    //-----------------------------------------------------
    // Assets methods section
    //-----------------------------------------------------

    /**
     * Returns editor related assets.
     *
     * @return AssetsCollection
     */
    abstract public function getEditorAssets();

    /**
     * Returns display related assets.
     *
     * @return AssetsCollection
     */
    abstract public function getDisplayAssets();

    //-----------------------------------------------------
    // Widget "display content" method section
    //-----------------------------------------------------

    /**
     * Makes widget "display content" HTML.
     *
     * @param ContentParserInterface $contentParser
     * @param array $properties
     * @param string $contentType
     * @param string|null $editorContent
     * @return string
     */
    public function makeDisplayContent(
        ContentParserInterface $contentParser,
        array $properties,
        $contentType,
        $editorContent = null
    ) {
        return '';
    }

}