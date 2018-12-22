<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/9/28 0028
 * Time: 下午 16:36
 */

namespace ChenPay;
abstract class Pay
{
    public $html = false;
    public $json = false;
    public $cookie = false;

    public function __construct($cookie = false)
    {
        date_default_timezone_set("PRC");
        $this->cookie = $cookie;
    }

    abstract protected function getData($url, $syncKey);

    abstract protected function DataHandle();

    abstract protected function DataContrast($fee, $time, $Minute);

    public static function Listen($s, $func)
    {
        ignore_user_abort();
        set_time_limit(0);
        do {
            $func();
            sleep($s);
        } while (true);
    }
}