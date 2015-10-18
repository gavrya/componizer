<?php
/**
 * Created by PhpStorm.
 * User: gavrya
 * Date: 10/15/15
 * Time: 9:08 PM
 */

namespace Gavrya\Componizer;


use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RecursiveRegexIterator;
use RegexIterator;
use SplFileInfo;

class Editor
{

    public static function run()
    {
        die('It works');
    }

    public static function search($path = '/Users/gavrya/Projects/componizer/vendor')
    {
        $matches = [];
        $dirIterator = new RecursiveDirectoryIterator($path);
        $iterator = new RecursiveIteratorIterator($dirIterator);
        foreach ($iterator as $fileInfo) {
            if($fileInfo instanceof SplFileInfo) {
                if($fileInfo->isReadable() && $fileInfo->getFilename() == 'selector_testcase.php') {
                    $matches[] = $fileInfo->getFilename();
                    // TODO: solve problem with matched multiple filenames in the same composer package
                    // write directory iterator filter to avoid unnesessary dir traversal and speed icreasing
                    // accept dir traversal until it reaches vendor/{vendor_name}/{vendor_package_name}/src/ dir including
                    // vendor/{vendor_name} - ACCEPT,
                    // vendor/{vendor_name}/{vendor_package_name} - ACCEPT,
                    // reaches vendor/{vendor_name}/{vendor_package_name}/src/ - ACCEPT,
                    // reaches vendor/{vendor_name}/{vendor_package_name}/src/somedir - IGNORE
                    // package file location vendor/{vendor_name}/{vendor_package_name}/src/ComponizerPackage.php
                    // http://php.net/manual/en/class.recursivecallbackfilteriterator.php
                    // http://php.net/manual/ru/class.recursivefilteriterator.php
                }
            }
        }

        var_dump($matches);
    }


}

Editor::search();