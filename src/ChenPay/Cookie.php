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
        try {
            $cookie = explode($name . '=', $cookie)[1];
            if ($name == 'uid') return explode('"', $cookie)[0];
            else return explode(';', $cookie)[0];
        } catch (\Exception $e) {
            throw new PayException('cookie有误', 445);
        }
    }
}