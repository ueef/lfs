<?php

namespace Ueef\Lfs\Exceptions {

    use Throwable;

    class Exception extends \Exception
    {
        public function __construct($message = "", $code = 0, Throwable $previous = null)
        {
            if (is_array($message)) {
                $message = call_user_func_array('sprintf', $message);
            }

            parent::__construct($message, $code, $previous);
        }
    }
}
