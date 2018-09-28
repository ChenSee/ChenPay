# 欢迎使用 **composer** 免签约支付宝与微信 带监听

- 免签约支付宝 根据COOKIE
- 免签约微信支付 根据COOKIE
- PHP程序自监听
- 我的站点[云](http://yun.9in.info)
- 我的博客[CHEN](http://9in.info)

### composer安装：
```
composer require ChenSee/ChenPay
```

### 使用教程：
```php
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
```

## 注意：

- 无法同时判断多人支付