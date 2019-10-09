<?php
/**
 * 两个支付必须分开运行，demo只是作为演示
 * Created by PhpStorm.
 * User: Chen
 * Date: 2018/9/28 0028
 * Time: 下午 17:16
 */
include __DIR__ . '/../vendor/autoload.php';
$aliCookie = '';
$wxCookie = '';

$GLOBALS['aliSum'] = 1;
ChenPay\Pay::Listen(10, function () use ($aliCookie) {
    // time 现在时间此为订单生成时间 默认3分钟有效时间
    $data = [['fee' => 0.01, 'time' => time() + 3 * 60]];
    try {
        $run = (new ChenPay\AliPay($aliCookie))->getData()->DataHandle();
        foreach ($data as $item) {
            $remarks = '123456'; //如果需要判断备注
            $order = $run->DataContrast($item['fee'], $item['time'], 5, $remarks);
            if ($order) echo "{$order}订单有效！备注：{$remarks}\n";
            unset($order, $item);// 摧毁变量防止内存溢出
        }
        echo $GLOBALS['aliSum'] . "次运行\n";
        $GLOBALS['aliSum']++;
    } catch (\ChenPay\PayException\PayException $e) {
        echo $e->getMessage() . "\n";
        unset($e);// 摧毁变量防止内存溢出
    }
    unset($run, $data);// 摧毁变量防止内存溢出
});

$GLOBALS['wxSum'] = 1;
$GLOBALS['syncKey'] = false;
ChenPay\Pay::Listen(10, function () use ($wxCookie) {
    // time 现在时间此为订单生成时间 默认3分钟有效时间
    $data = [['fee' => 0.01, 'time' => time() + 3 * 60]];
    try {
        $run = (new ChenPay\WxPay($wxCookie))->getData('wx.qq.com', $GLOBALS['syncKey'])->DataHandle();
        $GLOBALS['syncKey'] = $run->syncKey;
        foreach ($data as $item) {
            $remarks = '123456'; //如果需要判断备注
            $order = $run->DataContrast($item['fee'], $item['time'], 3, $remarks);
            if ($order) echo "{$order}订单有效！备注：{$remarks}\n";
            unset($order, $item);// 摧毁变量防止内存溢出
        }
        echo $GLOBALS['wxSum'] . "次运行\n";
        $GLOBALS['wxSum']++;
    } catch (\ChenPay\PayException\PayException $e) {
        echo $e->getMessage() . "\n";
        unset($e);// 摧毁变量防止内存溢出
    }
    unset($run, $data);// 摧毁变量防止内存溢出
});