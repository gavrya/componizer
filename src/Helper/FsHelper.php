<?php
/**
 * Created by PhpStorm.
 * User: gavrya
 * Date: 10/15/15
 * Time: 9:08 PM
 */

namespace Gavrya\Componizer\Helper;


use DirectoryIterator;
use Exception;
use FilesystemIterator;
use Gavrya\Componizer\Skeleton\ComponizerException;
use RecursiveCallbackFilterIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

class FsHelper
{

    //-----------------------------------------------------
    // Helpers section
    //-----------------------------------------------------

    public function composerVendorDir()
    {
        $path = dirname(__FILE__);

        for ($i = 0; $i < 10; $i++) {
            $path = dirname($path);

            if (basename($path) === 'vendor') {
                // case: when installed as composer package
                // example: /vendor/gavrya/componizer/src/Helper/FsHelper.php
                return $path;
            }

            $alternativePath = rtrim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'vendor';

            if (file_exists($alternativePath) && is_dir($alternativePath)) {
                // case: when installed from github as project
                // example: |- /componizer/src/Helper/FsHelper.php
                //          |- /vendor/...
                return $alternativePath;
            }
        }

        throw new ComponizerException('Composer vendor directory not found');
    }

    //-----------------------------------------------------
    // Plugins search section
    //-----------------------------------------------------

    public function pluginsJsonFiles($path, $fileName)
    {
        $jsonFiles = [];

        $dirIterator = new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS);

        $filteredIterator = new RecursiveCallbackFilterIterator($dirIterator,
            function ($current, $key, $iterator) use ($fileName) {
                if ($current instanceof SplFileInfo) {
                    $realPath = $current->getRealPath();

                    // last index of
                    $index = strrpos($realPath, DIRECTORY_SEPARATOR . 'vendor');

                    if ($index === false) {
                        return false;
                    }

                    // path elements
                    $elements = array_filter(explode(DIRECTORY_SEPARATOR, substr($realPath, $index)));

                    // path elements count
                    $elementsCount = count($elements);

                    if ($current->isDir() && $elementsCount === 2 && $current->getFilename() === 'composer') {
                        // case: /vendor/composer
                        return false;
                    } elseif ($current->isDir() && $elementsCount <= 3) {
                        // case: /vendor or /vendor/* or /vendor/*/*
                        return true;
                    } elseif ($current->isFile() && $elementsCount === 4 && $current->getFilename() === $fileName) {
                        // case: /vendor/*/*/$fileName
                        return true;
                    }
                }

                return false;
            }
        );

        $iterator = new RecursiveIteratorIterator($filteredIterator);

        foreach ($iterator as $fileInfo) {
            if ($fileInfo instanceof SplFileInfo && $fileInfo->isFile() && $fileInfo->getFilename() === $fileName) {
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
                }
            }
        }

        return $jsonData;
    }

    //-----------------------------------------------------
    // Symlinks section
    //-----------------------------------------------------

    public function createSymlink($sourceDir, $targetDir)
    {
        if (!file_exists($sourceDir) || !is_dir($sourceDir)) {
            return false;
        }

        // remove broken symlink
        if (is_link($targetDir) && $sourceDir !== realpath($targetDir)) {
            $this->removeSymlink($targetDir);
        }

        if (is_link($targetDir)) {
            return true;
        }

        return symlink($sourceDir, $targetDir);
    }

    public function removeSymlink($targetDir)
    {
        if (!file_exists($targetDir) || !is_link($targetDir)) {
            return false;
        }

        return unlink($targetDir) || rmdir($targetDir);
    }

    public function removeBrokenSymlinks($dir)
    {
        if (!file_exists($dir) || !is_dir($dir)) {
            return;
        }

        $dirIterator = new DirectoryIterator($dir);

        foreach ($dirIterator as $fileInfo) {
            if ($fileInfo instanceof SplFileInfo) {
                $linkPath = $fileInfo->getPathname();
                $targetPath = $fileInfo->getRealPath();

                if ($fileInfo->isLink() && (!file_exists($targetPath) || !is_dir($targetPath))) {
                    $this->removeSymlink($linkPath);
                }
            }
        }
    }

    //-----------------------------------------------------
    // Dirs section
    //-----------------------------------------------------

    public function makeDir($dir)
    {
        if (!file_exists($dir) || !is_dir($dir)) {
            mkdir($dir);
        }
    }

    public function removeDir($dir)
    {
        if(!file_exists($dir) || !is_dir($dir)) {
            return;
        }

        $dirIterator = new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS);
        $iterator = new RecursiveIteratorIterator($dirIterator, RecursiveIteratorIterator::CHILD_FIRST);

        foreach ($iterator as $path) {
            if ($path instanceof SplFileInfo) {
                $path->isDir() && !$path->isLink() ? rmdir($path->getPathname()) : unlink($path->getPathname());
            }
        }

        rmdir($dir);
    }

    public function removeDirs($dir, $dirNames)
    {
        if (!file_exists($dir) || !is_dir($dir) || empty($dirNames)) {
            return;
        }

        $dirIterator = new DirectoryIterator($dir);

        foreach ($dirIterator as $fileInfo) {
            if ($fileInfo instanceof DirectoryIterator) {
                if ($fileInfo->isDir() && !$fileInfo->isDot() && !in_array($fileInfo->getFilename(), $dirNames)) {
                    $this->removeDir($fileInfo->getPathname());
                }
            }
        }
    }

}
