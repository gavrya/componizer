<?php
/**
 * Created by PhpStorm.
 * User: gavrya
 * Date: 10/15/15
 * Time: 9:08 PM
 */

namespace Gavrya\Gravitizer\Helper;


use RecursiveCallbackFilterIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

class FsHelper
{

    public static function run()
    {
        die('It works');

        // 1) Get array of componizer package files (full path to ComponizerPackage.php files inside composer package)
        // 2) Get array of full class names with namespaces (parse class name, namespace and some interface implementation check)
        // 3) Get array of componizer package object instances from class names
    }

    public static function searchPackageFiles($path = '/Users/gavrya/Projects/Gravitizer/vendor', $fileName = 'ComponizerPackage.php')
    {
        $matches = [];
        $dirIterator = new RecursiveDirectoryIterator($path);
        // filter for directory iterator
        $filteredIterator = new RecursiveCallbackFilterIterator($dirIterator, function ($current, $key, $iterator) use ($fileName) {
            if ($current instanceof SplFileInfo) {
                $realPath = $current->getRealPath();
                // last position index
                $index = strrpos($realPath, DIRECTORY_SEPARATOR . 'vendor');
                if ($index === false) {
                    return false;
                }
                $targetPath = substr($realPath, $index);
                // path elements
                $elements = array_filter(explode(DIRECTORY_SEPARATOR, $targetPath));
                $elementsCount = count($elements);
                // check path elements
                /*
                $exclude = ['composer', 'laravel', 'yiisoft', 'symfony', 'swiftmailer', 'phpunit', 'doctrine', 'bin'];
                if ($current->isDir() && $elementsCount == 2 && in_array($elements[1], $exclude)) {
                    // case: /vendor/(composer|laravel|...) for ignoring some popular packages dirs
                    return false;
                } else
                */
                if ($current->isDir() && $elementsCount <= 3) {
                    // case: /vendor or /vendor/* or /vendor/*/*
                    return true;
                } elseif ($current->isDir() && $elementsCount == 4 && $elements[3] == 'src') {
                    // case: /vendor/*/*/src
                    return true;
                } elseif ($current->isFile() && $elementsCount == 5 && $elements[3] == 'src' && $elements[4] == $fileName) {
                    // case: /vendor/*/*/src/ComponizerPackage.php
                    return true;
                }
            }
            return false;
        });

        $iterator = new RecursiveIteratorIterator($filteredIterator);
        foreach ($iterator as $fileInfo) {
            if ($fileInfo instanceof SplFileInfo) {
                $matches[] = $fileInfo;
            }
        }

        var_dump($matches);

        return $matches;
    }


}

FsHelper::search();

//$path = '/vendor/vendor_name/vendor_package_name/src/ComponizerPackage.php';

//$explode = explode(DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR, $path);

//var_dump($explode);