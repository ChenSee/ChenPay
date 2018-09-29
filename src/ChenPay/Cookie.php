<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/9/28 0028
 * Time: 下午 16:44
 */

namespace ChenPay;

use ChenPay\PayException\PayException;

class Cookie
{
    /**
     * 获取cookie某key值
     * @param string $name
     * @param bool $cookie
     * @return mixed
     * @throws PayException
     */
    public static function getCookieName($name = 'uid', $cookie = false)
    {
        $getCookie = explode($name . '=', $cookie);
        if (count($getCookie) <= 1) throw new PayException('cookie有误', 445);
        if ($name == 'uid') return explode('"', $getCookie[1])[0];
        else return explode(';', $getCookie[1])[0];
    }
}