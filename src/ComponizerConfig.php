<?php

/**
 * Created by PhpStorm.
 * User: gavrya
 * Date: 12/26/15
 * Time: 4:39 PM
 */

namespace Gavrya\Componizer;


use Exception;
use InvalidArgumentException;

class ComponizerConfig
{

    // Config keys
    const CONFIG_LANG = 'lang_code';
    const CONFIG_CACHE_DIR = 'cache_dir';
    const CONFIG_PUBLIC_DIR = 'public_dir';
    const CONFIG_PREVIEW_URL = 'preview_url';

    // Internal variables
    private $config = [];
    private $validationErrors = [];

    //-----------------------------------------------------
    // Constructor section
    //-----------------------------------------------------

    public function __construct(array $config)
    {
        if (!is_array($config)) {
            throw new InvalidArgumentException('Invalid config');
        }

        $keys = [
            static::CONFIG_LANG,
            static::CONFIG_CACHE_DIR,
            static::CONFIG_PUBLIC_DIR,
            static::CONFIG_PREVIEW_URL,
        ];

        // leave only config related keys and values
        $this->config = array_intersect_key($config, array_flip($keys));

        $this->validateConfig();
    }

    //-----------------------------------------------------
    // Config related methods section
    //-----------------------------------------------------

    public function get($configKey, $defaultValue = null)
    {
        return isset($this->config[$configKey]) ? $this->config[$configKey] : $defaultValue;
    }

    public function isValid()
    {
        return empty($this->validationErrors);
    }

    public function getValidationErrors()
    {
        return $this->validationErrors;
    }

    //-----------------------------------------------------
    // Validation related methods section
    //-----------------------------------------------------

    private function validateConfig()
    {
        $this->validateLang();
        $this->validateCacheDir();
        $this->validatePublicDir();
        $this->validatePreviewUrl();
    }

    private function validateLang()
    {
        try {
            if (!isset($this->config[static::CONFIG_LANG])) {
                $this->config[static::CONFIG_LANG] = 'en';
            }

            if (!in_array($this->config[static::CONFIG_LANG], ['en', 'ru'])) {
                throw new Exception('Invalid language code');
            }
        } catch (Exception $ex) {
            $this->addValidationError(static::CONFIG_LANG, $ex->getMessage());
        }
    }

    private function validateCacheDir()
    {
        try {
            if (!isset($this->config[static::CONFIG_CACHE_DIR])) {
                throw new Exception('Invalid cache directory');
            }

            $dirPath = $this->config[static::CONFIG_CACHE_DIR];

            if (!file_exists($dirPath)) {
                throw new Exception('Cache directory is not exists');
            }

            if (is_link($dirPath) || !is_dir($dirPath)) {
                throw new Exception('Cache directory is not a valid directory');
            }

            if (!is_writable($dirPath)) {
                throw new Exception('Cache directory is not writable');
            }

            $dirPath = realpath($dirPath);

            $this->config[static::CONFIG_CACHE_DIR] = $dirPath;

            if (basename($dirPath) != static::CACHE_DIR_NAME) {
                $dirPath = $dirPath . DIRECTORY_SEPARATOR . static::CACHE_DIR_NAME;

                if (file_exists($dirPath) && is_dir($dirPath) && !is_writable($dirPath)) {
                    throw new Exception('Cache directory is not writable');
                }

                if ((!file_exists($dirPath) || !is_dir($dirPath)) && !mkdir($dirPath)) {
                    throw new Exception('Unable to create cache directory');
                }

                $this->config[static::CONFIG_CACHE_DIR] = $dirPath;
            }
        } catch (Exception $ex) {
            $this->addValidationError(static::CONFIG_CACHE_DIR, $ex->getMessage());
        }
    }

    private function validatePublicDir()
    {
        try {
            if (!isset($this->config[static::CONFIG_PUBLIC_DIR])) {
                throw new Exception('Invalid public directory');
            }

            $dirPath = $this->config[static::CONFIG_PUBLIC_DIR];

            if (!file_exists($dirPath)) {
                throw new Exception('Public directory is not exists');
            }

            if (is_link($dirPath) || !is_dir($dirPath)) {
                throw new Exception('Public directory is not a directory');
            }

            if (!is_writable($dirPath)) {
                throw new Exception('Public directory is not writable');
            }

            $dirPath = realpath($dirPath);

            $this->config[static::CONFIG_PUBLIC_DIR] = $dirPath;

            if (basename($dirPath) != static::PUBLIC_DIR_NAME) {
                $dirPath = $dirPath . DIRECTORY_SEPARATOR . static::PUBLIC_DIR_NAME;

                if (file_exists($dirPath) && is_dir($dirPath) && !is_writable($dirPath)) {
                    throw new Exception('Public directory is not writable');
                }

                if ((!file_exists($dirPath) || !is_dir($dirPath)) && !mkdir($dirPath)) {
                    throw new Exception('Unable to create public directory');
                }

                $this->config[static::CONFIG_PUBLIC_DIR] = $dirPath;
            }
        } catch (Exception $ex) {
            $this->addValidationError(static::CONFIG_PUBLIC_DIR, $ex->getMessage());
        }
    }

    private function validatePreviewUrl()
    {
        try {
            if (!isset($this->config[static::CONFIG_PREVIEW_URL])) {
                throw new Exception('Invalid preview url');
            }
        } catch (Exception $ex) {
            $this->addValidationError(static::CONFIG_PREVIEW_URL, $ex->getMessage());
        }
    }

    private function addValidationError($configKey, $message)
    {
        $this->validationErrors[$configKey][] = $message;
    }

}