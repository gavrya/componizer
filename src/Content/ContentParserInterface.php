<?php
/**
 * Created by PhpStorm.
 * User: gavrya
 * Date: 12/22/15
 * Time: 9:18 PM
 */

namespace Gavrya\Componizer\Content;


interface ContentParserInterface
{

    // Widget attributes
    const WIDGET_ATTR = 'data-componizer-widget';
    const WIDGET_ATTR_ID = 'data-componizer-widget-id';
    const WIDGET_ATTR_NAME = 'data-componizer-widget-name';
    const WIDGET_ATTR_PROPERTIES = 'data-componizer-widget-properties';
    const WIDGET_ATTR_CONTENT_TYPE = 'data-componizer-widget-content-type';
    const WIDGET_ATTR_CONTENT = 'data-componizer-widget-content';

    // Widget content types
    const WIDGET_CT_NONE = 'none';
    const WIDGET_CT_CODE = 'code';
    const WIDGET_CT_PLAIN_TEXT = 'plain_text';
    const WIDGET_CT_RICH_TEXT = 'rich_text';
    const WIDGET_CT_MIXED = 'mixed';

    /**
     * Parse provided "editor content" to "display content" representation.
     *
     * Parsing is processed based on allowed/disabled plugins/components and other settings from SettingsManager.
     *
     * @param string $editorContent Editor content to parse
     * @return string Parsed display content HTML or empty string
     */
    public function parseDisplayContent($editorContent);

}