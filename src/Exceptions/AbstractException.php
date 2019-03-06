<?php
declare(strict_types=1);

namespace Ueef\Lfs\Exceptions;

use Throwable;
use Exception;
use Ueef\Lfs\Interfaces\ExceptionInterface;

abstract class AbstractException extends Exception implements ExceptionInterface
{
    public function __construct($message = "", Throwable $previous = null)
    {
        if (is_array($message)) {
            foreach ($message as &$value) {
                if (is_array($value) || is_object($value)) {
                    $value = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
                }
            }

            $message = sprintf(...$message);
        }

        parent::__construct($message, 0, $previous);
    }
}
