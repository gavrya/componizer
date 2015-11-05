<?php
/**
 * Created by PhpStorm.
 * User: gavrya
 * Date: 10/28/15
 * Time: 2:34 PM
 */

namespace Gavrya\Componizer\Helper;


use Gavrya\Componizer\Skeleton\ComponizerException;

class StorageHelper
{

    // Cache dir
    private $cacheDir = null;

    // Storage data
    private $data = [];

    //-----------------------------------------------------
    // Instance creation/init section
    //-----------------------------------------------------

    public function __construct($cacheDir, $fileName = 'storage.json')
    {
        $this->cacheDir = $cacheDir;
        $this->storageFile = $this->cacheDir . DIRECTORY_SEPARATOR . $fileName;
        $this->init();
    }

    private function init()
    {
        if (!file_exists($this->cacheDir) || !is_dir($this->cacheDir) || !is_readable($this->cacheDir) || !is_writable($this->cacheDir)) {
            throw new ComponizerException('Invalid storage cache dir');
        }

        if ((!file_exists($this->storageFile) || !is_file($this->storageFile)) && !touch($this->storageFile) && !chmod($this->storageFile, 0777)) {
            throw new ComponizerException('Unable to create storage file');
        }

        if (!file_exists($this->storageFile) || !is_file($this->storageFile) || !is_readable($this->storageFile) || !is_writable($this->storageFile)) {
            throw new ComponizerException('Invalid storage file');
        }

        $jsonData = json_decode(file_get_contents($this->storageFile), true);

        $this->data = is_array($jsonData) ? $jsonData : [];
    }

    //-----------------------------------------------------
    // Methods section
    //-----------------------------------------------------

    public function all()
    {
        return $this->data;
    }

    public function get($key, $default = null)
    {
        return $key !== null && $this->has($key) ? $this->data[$key] : $default;
    }

    public function set($key, $value)
    {
        $this->data[$key] = $value;
    }

    public function has($key)
    {
        return array_key_exists($key, $this->data);
    }

    public function del($key = null)
    {
        if ($this->has($key)) {
            unset($this->data[$key]);
        }
    }

    public function save()
    {
        $jsonData = json_encode($this->data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        return (bool) file_put_contents($this->storageFile, $jsonData, LOCK_EX);
    }

}