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
$GLOBALS['AliStatus'] = time(); // 暂停 有订单情况下才是10秒一次的频率 杜绝支付宝风控
ChenPay\Pay::Listen(10, function () use ($AliCookie) {
    // time 现在时间此为订单生成时间 默认3分钟有效时间
    $data = [['fee' => 0.01, 'time' => time() + 3 * 60]];
    if ($GLOBALS['AliStatus'] > time() && count($data) == 0) return;
    try {
        $run = (new ChenPay\AliPay($AliCookie))->getData($GLOBALS['AliType'])->DataHandle();
        foreach ($data as $item) {
            $Remarks = '123456'; //如果需要判断备注
            $order = $run->DataContrast($item['fee'], $item['time'], 5, $Remarks);
            if ($order) echo "{$order}订单有效！备注：{$Remarks}\n";
            unset($order, $item);// 摧毁变量防止内存溢出
        }
        echo $GLOBALS['AliSum'] . "次运行\n";
        $GLOBALS['AliType'] = !$GLOBALS['AliType'];
        $GLOBALS['AliSum']++;
        $GLOBALS['AliStatus'] = time() + 2 * 60; //
    } catch (\ChenPay\PayException\PayException $e) {
        echo $e->getMessage() . "\n";
        unset($e);// 摧毁变量防止内存溢出
    }
    unset($run, $data);// 摧毁变量防止内存溢出
});

$GLOBALS['WxSum'] = 1;
$GLOBALS['syncKey'] = false;
ChenPay\Pay::Listen(10, function () use ($WxCookie) {
    // time 现在时间此为订单生成时间 默认3分钟有效时间
    $data = [['fee' => 0.01, 'time' => time() + 3 * 60]];
    try {
        $run = (new ChenPay\WxPay($WxCookie))->getData('wx.qq.com', $GLOBALS['syncKey'])->DataHandle();
        $GLOBALS['syncKey'] = $run->syncKey;
        foreach ($data as $item) {
            $Remarks = '123456'; //如果需要判断备注
            $order = $run->DataContrast($item['fee'], $item['time'], 3, $Remarks);
            if ($order) echo "{$order}订单有效！备注：{$Remarks}\n";
            unset($order, $item);// 摧毁变量防止内存溢出
        }
        echo $GLOBALS['WxSum'] . "次运行\n";
        $GLOBALS['WxSum']++;
    } catch (\ChenPay\PayException\PayException $e) {
        echo $e->getMessage() . "\n";
        unset($e);// 摧毁变量防止内存溢出
    }
    unset($run, $data);// 摧毁变量防止内存溢出
});