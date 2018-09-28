<?php
/**
 * Created by PhpStorm.
 * User: Chen
 * Date: 2018/9/28 0028
 * Time: 下午 17:16
 */
include __DIR__ . '/../vendor/autoload.php';
$AliCookie = '';
$WxCookie = '';

$GLOBALS['sum'] = 1;
ChenPay\Pay::Listen(10, function () use ($AliCookie) {
    $data = [['fee' => 0.01, 'time' => time() + 3 * 60]];
    try {
        $run = (new ChenPay\AliPay($AliCookie))->getData()->DataHandle();
        foreach ($data as $item) {
            $order = $run->DataContrast($item['fee'], $item['time']);
            if ($order) echo $order . "订单有效！\n";
        }
        echo $GLOBALS['sum'] . "\n";
        $GLOBALS['sum']++;
    } catch (\ChenPay\PayException\PayException $e) {
        echo $e->getMessage();
    }
});

$GLOBALS['syncKey'] = false;
ChenPay\Pay::Listen(10, function () use ($WxCookie) {
    $data = [['fee' => 0.01, 'time' => time() + 3 * 60]];
    try {
        $run = (new ChenPay\WxPay($WxCookie))->getData('wx2.qq.com', $GLOBALS['syncKey'])->DataHandle();
        $GLOBALS['syncKey'] = $run->syncKey;
        foreach ($data as $item) {
            $order = $run->DataContrast($item['fee'], $item['time']);
            if ($order) echo $order . "订单有效！\n";
        }
        echo $GLOBALS['sum'] . "\n";
        $GLOBALS['sum']++;
    } catch (\ChenPay\PayException\PayException $e) {
        echo $e->getMessage();
    }
});
