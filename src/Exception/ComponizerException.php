<?php
/**
 * Created by PhpStorm.
 * User: gavrya
 * Date: 10/23/15
 * Time: 5:17 PM
 */

namespace Gavrya\Componizer\Exception;


use Exception;

/**
 * Describes a generic exception throwed by the library or any of its component.
 *
 * @package Gavrya\Componizer\Skeleton
 */
class ComponizerException extends Exception
{
    // Exception codes
    const EX_ERROR = 0;
    const EX_CONFIG_SETUP_ERROR = 100;
    const EX_LANG_INVALID = 200;
    const EX_CACHE_DIR_INVALID = 300;
    const EX_CACHE_DIR_NOT_EXISTS = 301;
    const EX_CACHE_DIR_NOT_DIRECTORY = 302;
    const EX_CACHE_DIR_NOT_WRITABLE = 303;
    const EX_CACHE_DIR_UNABLE_CREATE = 304;
    const EX_PUBLIC_DIR_INVALID = 400;
    const EX_PUBLIC_DIR_NOT_EXISTS = 401;
    const EX_PUBLIC_DIR_NOT_DIRECTORY = 402;
    const EX_PUBLIC_DIR_NOT_WRITABLE = 403;
    const EX_PUBLIC_DIR_UNABLE_CREATE = 404;
    const EX_PREVIEW_URL_INVALID = 500;

    /**
     * ComponizerException constructor.
     *
     * @param string $message Exception message
     * @param int $code One of the constants exception code
     * @param Exception|null $previous Optional previous exception
     */
    public function __construct($message = '', $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

}