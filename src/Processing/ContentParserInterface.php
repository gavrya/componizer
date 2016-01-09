<?php
/**
 * Created by PhpStorm.
 * User: gavrya
 * Date: 12/22/15
 * Time: 9:18 PM
 */

namespace Gavrya\Componizer\Processing;


interface ContentParserInterface
{

    /**
     * Parses provided "editor content" to "display content".
     *
     * Parsing is processed based on allowed/disabled plugins/components and other settings from SettingsManager.
     *
     * @param string $editorContent
     * @return string
     */
    public function parseDisplayContent($editorContent);

}