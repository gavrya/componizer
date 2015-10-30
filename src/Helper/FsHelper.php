<?php
/**
 * Created by PhpStorm.
 * User: gavrya
 * Date: 10/15/15
 * Time: 9:08 PM
 */

namespace Gavrya\Gravitizer\Helper;


use Exception;
use Gavrya\Gravitizer\Skeleton\GravitizerException;
use RecursiveCallbackFilterIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

class FsHelper
{

    public function composerVendorDir()
    {
        $path = dirname(__FILE__);
        for ($i = 0; $i < 10; $i++) {
            $path = dirname($path);
            if (basename($path) == 'vendor') { // check if this working on windows?
                // case: when installed as composer package
                // example: /vendor/gavrya/gravitizer/src/Helper/FsHelper.php
                return $path;
            }
            $alternativePath = rtrim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'vendor';
            if (file_exists($alternativePath)) {
                // case: when installed from github as project
                // example: |- /gravitizer/src/Helper/FsHelper.php
                //          |- /vendor/...
                return $alternativePath;
            }
        }
        throw new GravitizerException('Composer vendor directory not found');
    }

    public function pluginsJsonFiles($path, $fileName)
    {
        $jsonFiles = [];
        $dirIterator = new RecursiveDirectoryIterator($path);
        // filter for directory iterator
        $filteredIterator = new RecursiveCallbackFilterIterator($dirIterator,
            function ($current, $key, $iterator) use ($fileName) {
                if ($current instanceof SplFileInfo) {
                    $realPath = $current->getRealPath();
                    // last position index
                    $index = strrpos($realPath, DIRECTORY_SEPARATOR . 'vendor');
                    if ($index === false) {
                        return false;
                    }
                    // path elements
                    $elements = array_filter(explode(DIRECTORY_SEPARATOR, substr($realPath, $index)));
                    $elementsCount = count($elements);
                    // check path elements
                    if ($current->isDir() && $elementsCount == 2 && $current->getFilename() == 'composer') {
                        // case: /vendor/composer
                        return false;
                    } elseif ($current->isDir() && $elementsCount <= 3) {
                        // case: /vendor or /vendor/* or /vendor/*/*
                        return true;
                    } elseif ($current->isFile() && $elementsCount == 4 && $current->getFilename() == $fileName) {
                        // case: /vendor/*/*/$fileName
                        return true;
                    }
                }

                return false;
            }
        );

        $iterator = new RecursiveIteratorIterator($filteredIterator);
        foreach ($iterator as $fileInfo) {
            if ($fileInfo instanceof SplFileInfo && $fileInfo->isFile() && $fileInfo->getFilename() == $fileName) {
                $jsonFiles[] = $fileInfo;
            }
        }

        return $jsonFiles;
    }

    public function pluginsJsonData(array $jsonFiles)
    {
        $jsonData = [];
        foreach ($jsonFiles as $jsonFile) {
            if ($jsonFile instanceof SplFileInfo && $jsonFile->isReadable()) {
                try {
                    $jsonString = $jsonFile->openFile()->fread($jsonFile->getSize());
                    $data = json_decode($jsonString, true);
                    if (is_array($data) && !empty($data)) {
                        $jsonData[] = $data;
                    }
                } catch (Exception $ex) {
                    // ignoring
                }
            }
        }

        return $jsonData;
    }

}
