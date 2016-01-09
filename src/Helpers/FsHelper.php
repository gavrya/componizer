<?php
/**
 * Created by PhpStorm.
 * User: gavrya
 * Date: 10/15/15
 * Time: 9:08 PM
 */

namespace Gavrya\Componizer\Helpers;


use DirectoryIterator;
use Exception;
use FilesystemIterator;
use RecursiveCallbackFilterIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

/**
 * Class FsHelper contains helpfull methods for working with file system.
 *
 * @package Gavrya\Componizer\Helpers
 */
class FsHelper
{

    // Composer related dirs
    const COMPOSER_DIR = 'composer';
    const COMPOSER_VENDOR_DIR = 'vendor';

    //-----------------------------------------------------
    // Composer related methods section
    //-----------------------------------------------------

    /**
     * Returns absolute path to the composer vendor directory.
     *
     * @return string|null
     */
    public function getComposerVendorDir()
    {
        $path = dirname(__FILE__);

        for ($i = 0; $i < 10; $i++) {
            $path = dirname($path);

            if (basename($path) === self::COMPOSER_VENDOR_DIR) {
                // case: when installed as composer package
                // example: /vendor/gavrya/componizer/src/Helpers/FsHelper.php
                return $path;
            }

            $alternativePath = $path . DIRECTORY_SEPARATOR . self::COMPOSER_VENDOR_DIR;

            if (file_exists($alternativePath) && is_dir($alternativePath)) {
                // case: when installed from github as project
                // example: |- /componizer/src/Helpers/FsHelper.php
                //          |- /vendor/...
                return $alternativePath;
            }
        }

        return null;
    }

    //-----------------------------------------------------
    // Plugins related methods section
    //-----------------------------------------------------

    /**
     * Returns componizer plugins JSON files.
     *
     * @param string $path
     * @param string $fileName
     * @return SplFileInfo[]|array
     */
    public function getPluginsJsonFiles($path, $fileName)
    {
        $jsonFiles = [];

        $dirIterator = new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS);

        $filteredIterator = new RecursiveCallbackFilterIterator($dirIterator,

            function ($file, $key, $iterator) use ($fileName) {
                /** @var SplFileInfo $file */
                $filePath = $file->getRealPath();

                $position = strrpos($filePath, DIRECTORY_SEPARATOR . self::COMPOSER_VENDOR_DIR);

                if ($position === false) {
                    return false;
                }

                $pathElements = array_filter(explode(DIRECTORY_SEPARATOR, substr($filePath, $position)));
                $pathElementsNum = count($pathElements);

                if ($file->isDir() && $pathElementsNum === 2 && $file->getFilename() === self::COMPOSER_DIR) {
                    // case: /vendor/composer
                    return false;
                }

                if ($file->isDir() && $pathElementsNum <= 3) {
                    // case: /vendor or /vendor/* or /vendor/*/*
                    return true;
                }

                if ($file->isFile() && $pathElementsNum === 4 && $file->getFilename() === $fileName) {
                    // case: /vendor/*/*/$fileName
                    return true;
                }

                return false;
            }
        );

        $iterator = new RecursiveIteratorIterator($filteredIterator);

        /** @var SplFileInfo $file */
        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getFilename() === $fileName) {
                $jsonFiles[] = $file;
            }
        }

        return $jsonFiles;
    }

    /**
     * Returns collected plugins JSON data from provided list of plugins JSON files.
     *
     * @param array $jsonFiles
     * @return array
     */
    public function getPluginsJsonData(array $jsonFiles)
    {
        $jsonData = [];

        foreach ($jsonFiles as $file) {
            if ($file instanceof SplFileInfo && $file->isReadable()) {
                try {
                    $pluginJson = $file->openFile()->fread($file->getSize());
                    $pluginData = json_decode($pluginJson, true);

                    if (is_array($pluginData) && !empty($pluginData)) {
                        $jsonData[] = $pluginData;
                    }
                } catch (Exception $ex) {
                }
            }
        }

        return $jsonData;
    }

    //-----------------------------------------------------
    // Symlinks related methods section
    //-----------------------------------------------------

    /**
     * Creates a symbolic link from source to the existing target directory.
     *
     * @param string $sourceDir
     * @param string $targetDir
     * @return bool
     */
    public function createSymlink($sourceDir, $targetDir)
    {
        if (!file_exists($sourceDir) || !is_dir($sourceDir)) {
            return false;
        }

        if (is_link($targetDir) && $sourceDir !== realpath($targetDir)) {
            $this->removeSymlink($targetDir);
        }

        if (is_link($targetDir)) {
            return true;
        }

        return symlink($sourceDir, $targetDir);
    }

    /**
     * Removes a symbolic link with the source path.
     *
     * @param string $sourceDir
     * @return bool
     */
    public function removeSymlink($sourceDir)
    {
        if (!file_exists($sourceDir) || !is_link($sourceDir)) {
            return false;
        }

        return unlink($sourceDir) || rmdir($sourceDir);
    }

    /**
     * Iterates over directory and removes broken symlinks.
     *
     * @param string $dir
     */
    public function removeBrokenSymlinks($dir)
    {
        if (!file_exists($dir) || !is_dir($dir)) {
            return;
        }

        $dirIterator = new DirectoryIterator($dir);

        /** @var SplFileInfo $file */
        foreach ($dirIterator as $file) {
            $symlinkPath = $file->getPathname();
            $realPath = $file->getRealPath();

            if ($file->isLink() && (!file_exists($realPath) || !is_dir($realPath))) {
                $this->removeSymlink($symlinkPath);
            }
        }
    }

    //-----------------------------------------------------
    // Dirs related methods section
    //-----------------------------------------------------

    /**
     * Creates new directory if not already exists.
     *
     * @param string $dir
     */
    public function makeDir($dir)
    {
        if (!file_exists($dir) || !is_dir($dir)) {
            mkdir($dir);
        }
    }

    /**
     * Removes directory with all of the content inside it.
     *
     * @param string $dir
     */
    public function removeDir($dir)
    {
        if (!file_exists($dir) || !is_dir($dir)) {
            return;
        }

        $dirIterator = new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS);
        $iterator = new RecursiveIteratorIterator($dirIterator, RecursiveIteratorIterator::CHILD_FIRST);

        foreach ($iterator as $file) {
            /** @var SplFileInfo $file */
            $file->isDir() && !$file->isLink() ? rmdir($file->getPathname()) : unlink($file->getPathname());
        }

        rmdir($dir);
    }

    /**
     * Removes all directories inside target directory which names are not in list.
     *
     * @param string $dir
     * @param array $dirNames
     */
    public function removeDirs($dir, $dirNames)
    {
        if (!file_exists($dir) || !is_dir($dir) || empty($dirNames)) {
            return;
        }

        $dirIterator = new DirectoryIterator($dir);

        /** @var DirectoryIterator $file */
        foreach ($dirIterator as $file) {
            if ($file->isDir() && !$file->isDot() && !in_array($file->getFilename(), $dirNames)) {
                $this->removeDir($file->getPathname());
            }
        }
    }

}
