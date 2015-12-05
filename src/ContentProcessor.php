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

        /** @var ContentParser $contentParser */
        $contentParser = $this->componizer->resolve(ContentParser::class);

        // display content
        $displayContent = $contentParser->parseDisplayContent($editorContent);

        return $displayContent;
    }

    //-----------------------------------------------------
    // Assets section
    //-----------------------------------------------------

    public function componizerAssets()
    {

    }

    public function editorAssets()
    {

    }

    public function displayAssets()
    {

    }

}