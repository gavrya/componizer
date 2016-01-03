<?php
/**
 * Created by PhpStorm.
 * User: gavrya
 * Date: 10/28/15
 * Time: 2:34 PM
 */

namespace Gavrya\Componizer\Helper;

use InvalidArgumentException;


/**
 * Class StorageHelper provides helpfull methods for storing and retrieving data using JSON file.
 *
 * @package Gavrya\Componizer\Helper
 */
class StorageHelper
{

    /**
     * @var string
     */
    private $cacheDir = null;

    /**
     * @var string
     */
    private $storageFile = null;

    /**
     * @var array
     */
    private $data = [];

    //-----------------------------------------------------
    // Constructor section
    //-----------------------------------------------------

    /**
     * StorageHelper constructor.
     *
     * @param string $cacheDir
     * @param string $fileName
     */
    public function __construct($cacheDir, $fileName = 'storage.json')
    {
        $this->cacheDir = $cacheDir;
        $this->storageFile = $cacheDir . DIRECTORY_SEPARATOR . $fileName;
        $this->init();
    }

    /**
     * Loads storage data from JSON file.
     *
     * @throws InvalidArgumentException
     */
    private function init()
    {
        if (
            !file_exists($this->cacheDir) ||
            !is_dir($this->cacheDir) ||
            !is_readable($this->cacheDir) ||
            !is_writable($this->cacheDir)
        ) {
            throw new InvalidArgumentException(sprintf('Invalid storage cache dir: %s', $this->cacheDir));
        }

        if (
            (!file_exists($this->storageFile) || !is_file($this->storageFile)) &&
            (!touch($this->storageFile) || !chmod($this->storageFile, 0777))
        ) {
            throw new InvalidArgumentException(sprintf('Unable to create storage file: %s', $this->storageFile));
        }

        if (
            !file_exists($this->storageFile) ||
            !is_file($this->storageFile) ||
            !is_readable($this->storageFile) ||
            !is_writable($this->storageFile)
        ) {
            throw new InvalidArgumentException(sprintf('Unable to create storage file: %s', $this->storageFile));
        }

        $jsonData = json_decode(file_get_contents($this->storageFile), true);

        $this->data = is_array($jsonData) ? $jsonData : [];
    }

    //-----------------------------------------------------
    // General methods section
    //-----------------------------------------------------

    /**
     * Returns all storage data.
     *
     * @return array
     */
    public function getAll()
    {
        return $this->data;
    }

    /**
     * Returns key based data or default value if there is no data.
     *
     * @param string $key
     * @param mixed|null $default
     * @return mixed|null
     */
    public function get($key, $default = null)
    {
        return $key !== null && $this->has($key) ? $this->data[$key] : $default;
    }

    /**
     * Sets key based data.
     *
     * Use save() method to actually save storage data.
     * @see save
     *
     * @param string $key
     * @param mixed $value
     */
    public function set($key, $value)
    {
        $this->data[$key] = $value;
    }

    /**
     * Checks if key based data exist.
     *
     * @param string $key
     * @return bool
     */
    public function has($key)
    {
        return array_key_exists($key, $this->data);
    }

    /**
     * Deletes data from storage by key.
     *
     * All storage data will be deleted if storage key will not be passed.
     * Use save() method to actually save storage data.
     *
     * @see save
     *
     * @param string|null $key
     */
    public function delete($key = null)
    {
        if ($key === null) {
            $this->data = [];
        }

        if ($this->has($key)) {
            unset($this->data[$key]);
        }
    }

    /**
     * Saves storage data to disk by writing out storage JSON file.
     *
     * @return bool
     */
    public function save()
    {
        $jsonData = json_encode($this->data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        return (bool)file_put_contents($this->storageFile, $jsonData, LOCK_EX);
    }

}