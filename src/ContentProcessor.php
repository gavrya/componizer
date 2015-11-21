<?php
/**
 * Created by PhpStorm.
 * User: gavrya
 * Date: 11/9/15
 * Time: 8:31 PM
 */

namespace Gavrya\Componizer;


class ContentProcessor
{

    // Componizer
    private $componizer = null;

    // Internal variables
    private $editorContent = null;

    //-----------------------------------------------------
    // Instance creation/init section
    //-----------------------------------------------------

    public function __construct(Componizer $componizer)
    {
        $this->componizer = $componizer;
    }

    //-----------------------------------------------------
    // Content init/make section
    //-----------------------------------------------------

    public function initEditorContent($editorContent)
    {
        $this->editorContent = $editorContent;
    }

    public function makeDisplayContent($editorContent = null)
    {
        // check editor content
        if ($editorContent === null) {
            $editorContent = $this->editorContent;
        }

        // content parser
        $contentParser = $this->componizer->resolve(ContentParser::class);

        // display content
        $displayContent = $contentParser->parseDisplayContent($editorContent);

        return $displayContent;
    }

    //-----------------------------------------------------
    // Editor related assets section
    //-----------------------------------------------------

    public function editorCss($baseUrl = null)
    {

    }

    public function editorJs($baseUrl = null)
    {

    }

    public function editorJsModules($baseUrl = null)
    {

    }

    //-----------------------------------------------------
    // Display related assets section
    //-----------------------------------------------------

    public function displayCss($baseUrl = null)
    {

    }

    public function displayJs($baseUrl = null)
    {

    }

}