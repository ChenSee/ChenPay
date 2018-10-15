<?php
/**
 * 两个支付必须分开运行，demo只是作为演示
 * Created by PhpStorm.
 * User: Chen
 * Date: 2018/9/28 0028
 * Time: 下午 17:16
 */
include __DIR__ . '/../vendor/autoload.php';
$AliCookie = '';
$WxCookie = '';

$GLOBALS['AliSum'] = 1;
$GLOBALS['AliType'] = true; // 支付宝接口切换
ChenPay\Pay::Listen(10, function () use ($AliCookie) {
    // time 现在时间此为订单生成时间 默认3分钟有效时间
    $data = [['fee' => 0.01, 'time' => time() + 3 * 60]];
    try {
        $run = (new ChenPay\AliPay($AliCookie))->getData($GLOBALS['AliType'])->DataHandle();
        foreach ($data as $item) {
            $order = $run->DataContrast($item['fee'], $item['time']);
            if ($order) echo $order . "订单有效！\n";
        }
        echo $GLOBALS['AliSum'] . "次运行\n";
        $GLOBALS['AliType'] = !$GLOBALS['AliType'];
        $GLOBALS['AliSum']++;
    } catch (\ChenPay\PayException\PayException $e) {
        echo $e->getMessage() . "\n";
    }
});

$GLOBALS['WxSum'] = 1;
$GLOBALS['syncKey'] = false;
ChenPay\Pay::Listen(10, function () use ($WxCookie) {
    //  time 现在时间此为订单生成时间 默认3分钟有效时间
    $data = [['fee' => 0.01, 'time' => time() + 3 * 60]];
    try {
        $run = (new ChenPay\WxPay($WxCookie))->getData('wx2.qq.com', $GLOBALS['syncKey'])->DataHandle();
        $GLOBALS['syncKey'] = $run->syncKey;
        foreach ($data as $item) {
            $order = $run->DataContrast($item['fee'], $item['time']);
            if ($order) echo $order . "订单有效！\n";
        }
        echo $GLOBALS['WxSum'] . "次运行\n";
        $GLOBALS['WxSum']++;
    } catch (\ChenPay\PayException\PayException $e) {
        echo $e->getMessage() . "\n";
    }
});
