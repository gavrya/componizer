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
    private $requiredWidgets = [];

    private $componizerAssets = [];
    private $editorAssets = [];
    private $displayAssets = [];

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
        $this->requiredWidgets = [];

        if(!is_string($editorContent) || empty($editorContent)) {
            return;
        }

        // todo: detect required components needed in order to render "display content"
    }

    public function makeDisplayContent($editorContent)
    {
        // check editor content
        if ($editorContent === null) {
            return '';
        }

        /** @var ContentParser $contentParser */
        $contentParser = $this->componizer->resolve(ContentParser::class);

        // display content
        $displayContent = $contentParser->parseDisplayContent($editorContent);

        return $displayContent;
    }

    //-----------------------------------------------------
    // Required components section
    //-----------------------------------------------------

    public function requiredWidgets()
    {

    }

    //-----------------------------------------------------
    // Assets section
    //-----------------------------------------------------

    public function componizerAssets()
    {
        // todo: return componizer related assets needed in order to editor gets worked
    }

    public function editorAssets()
    {
        // todo: return editor related assets based on required components
    }

    public function displayAssets()
    {
        // todo: return display related assets based on required components
    }

}