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
 * Provides helpfull methods for storing and retrieving data based on JSON file.
 *
 * Class StorageHelper
 * @package Gavrya\Componizer\Helper
 */
class StorageHelper
{

    /**
     * @var string Absolute path to the cache directory where JSON file is located
     */
    private $cacheDir = null;

    /**
     * @var string Absolute path to the storage JSON file
     */
    private $storageFile = null;

    /**
     * @var array Storage data array
     */
    private $data = [];

    //-----------------------------------------------------
    // Construct section
    //-----------------------------------------------------

    /**
     * StorageHelper constructor.
     *
     * @param $cacheDir string Absolute path to the cache directory where JSON file is located (without trailing slash)
     * @param string $fileName Optional name of the storage JSON file, by default 'storage.json' will be used
     */
    public function __construct($cacheDir, $fileName = 'storage.json')
    {
        $this->cacheDir = $cacheDir;
        $this->storageFile = $cacheDir . DIRECTORY_SEPARATOR . $fileName;
        $this->init();
    }

    /**
     * Initiates storage data from JSON file.
     *
     * @throws InvalidArgumentException When one of the arguments was invalid
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
    // Basic methods section
    //-----------------------------------------------------

    /**
     * Returns storage data array.
     *
     * @return array Storage data array
     */
    public function all()
    {
        return $this->data;
    }

    /**
     * Returns key based data or default value if there is no data for provided key.
     *
     * @param string $key Key to search data for
     * @param mixed|null $default Default value to return
     * @return mixed|null Returned data from storage
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
     * @param string $key Storage key
     * @param mixed $value Data to store
     */
    public function set($key, $value)
    {
        $this->data[$key] = $value;
    }

    /**
     * Checks if key based data exist.
     *
     * @param string $key Storage key to check
     * @return bool true if data exists, false otherwise
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
     * @param null $key Storage key wich data need to be removed
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
     * @return bool true if storage JSON file was written successfully, false otherwise
     */
    public function save()
    {
        $jsonData = json_encode($this->data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        return (bool)file_put_contents($this->storageFile, $jsonData, LOCK_EX);
    }

}