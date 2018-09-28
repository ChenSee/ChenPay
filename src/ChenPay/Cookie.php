<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/9/28 0028
 * Time: 下午 16:44
 */

namespace ChenPay;
class Cookie
{
    /**
     * 获取cookie某key值
     * @param string $name
     * @param bool $cookie
     * @return mixed
     */
    public static function getCookieName($name = 'uid', $cookie = false)
    {
        $cookie = explode($name . '=', $cookie)[1];
        if ($name == 'uid') return explode('"', $cookie)[0];
        else return explode(';', $cookie)[0];
    }
}