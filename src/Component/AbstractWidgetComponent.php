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
 * Required in order to implement componizer widget.
 *
 * @package Gavrya\Componizer\Component
 */
abstract class AbstractWidgetComponent implements ComponentInterface
{

    //-----------------------------------------------------
    // Assets methods section
    //-----------------------------------------------------

    /**
     * Returns editor assets.
     *
     * Required for the editor widget related HTML.
     *
     * @return AssetsCollection Assets collection
     */
    abstract public function getEditorAssets();

    /**
     * Returns display assets.
     *
     * Required for the generated "display content" HTML.
     *
     * @return AssetsCollection Assets collection
     */
    abstract public function getDisplayAssets();

    //-----------------------------------------------------
    // Make display content section
    //-----------------------------------------------------

    /**
     * Generates widget "display content" HTML.
     *
     * @param ContentParserInterface $parser Parser util
     * @param array $properties Widget related JSON data
     * @param string $contentType Content type of the passed content
     * @param string|null $content Widget content in format of "editor content"
     * @return string Generated "display content" HTML
     * @internal param callable $parser Helper function for parsing "editor content" to the "display content"
     */
    public function makeDisplayContent(ContentParserInterface $parser, array $properties, $contentType, $content = null)
    {
        return '';
    }

}