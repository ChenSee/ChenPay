# 欢迎使用 **composer** 免签约支付宝与微信 带监听

- 免签约支付宝 根据COOKIE
- 免签约微信支付 根据COOKIE
- 实时到帐个人账户
- PHP程序自监听
- 我的站点[云](http://yun.9in.info)
- 我的博客[CHEN](http://9in.info)
### V1.0.5
- 更新支付宝双接口轮流切换API达到支付宝防止频繁访问阻止机制
- 如果单一接口出现阻止则会持续使用另外接口
### V1.0.6
- 增加支付宝频繁错误码446
### V1.0.7
- 10秒超时时间

### composer安装：
```
composer require chen-see/chen-pay
```

### 使用教程：
```php
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
            $order = $run->DataContrast($item['fee'], $item['time'],3);
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
    // time 现在时间此为订单生成时间 默认3分钟有效时间
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
```
### 获取支付宝COOKIE
- 浏览器访问：https://mbillexprod.alipay.com/enterprise/tradeListQuery.htm
- 登录支付宝账号
- 浏览器按f12再刷新一下
- 可以看到tradeListQuery.json
- 点击header它找到Cookie: 后面就是cookie全部复制到后台配置框内
### 获取微信COOKIE
- 浏览器访问：https://wx.qq.com（此地址必须设置到后台支付设置里，登录完成后会有所变更）
- 手机扫码登录微信账号
- 浏览器按f12再刷新一下
- 可以看到webwxinit?r=*******
- 点击header它找到Cookie: 后面有cookie了

### 运行：
```
# 前台运行
php test/test.php
# 后台运行
nohup php test/test.php &
```

## 注意：

- 无法同时判断相同价格多人支付
- 两个支付必须分开运行，demo只是作为演示
- 服务器时间必须是国内的时间，不然对不上支付宝微信时间