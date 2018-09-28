<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/9/28 0028
 * Time: 下午 16:40
 */

namespace ChenPay\PayException;
class PayException extends \Exception
{
    public function __construct($message, $code = 0, \Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    public function __toString()
    {
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }

    public function customFunction()
    {
        echo "A custom function for this type of exception\n";
    }
}