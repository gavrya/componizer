<?php
/**
 * Created by PhpStorm.
 * User: gavrya
 * Date: 10/23/15
 * Time: 5:17 PM
 */

namespace Gavrya\Componizer\Skeleton;


use Exception;

class ComponizerException extends Exception
{

    public function __construct($message = '', $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

}