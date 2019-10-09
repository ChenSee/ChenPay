# **Composer** 免签约支付宝与微信 带监听

- 免签约支付宝
- 免签约微信支付
- 实时到帐个人账户
- PHP程序自监听
### 讨论群
https://t.me/chenAirport
### DEMO测试
https://pay.n2.nu

### composer安装：
```
composer require chen-see/chen-pay
```

### 使用教程：
```php
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
```

### 获取支付宝COOKIE
- 浏览器访问：https://mbillexprod.alipay.com/enterprise/tradeListQuery.htm
- 登录支付宝账号
- 浏览器按f12
- 找到Network并点击再刷新一下
- 可以看到tradeListQuery.json点击它
- 点击headers它找到Cookie: 后面就是cookie(务必复制完整)

### 获取微信COOKIE
- 浏览器访问：https://wx.qq.com（此地址必须设置到后台支付设置里，登录完成后会有所变更）
- 手机扫码登录微信账号
- 浏览器按f12
- 找到Network并点击再刷新一下
- 可以看到webwxinit?r=*******点击它
- 点击headers它找到Cookie: 后面就是cookie(务必复制完整)

### 运行：
```
# 前台运行
php test/test.php
# 后台运行
nohup php test/test.php &
```

## 注意：
- 支付宝需开通商家服务 (手机支付宝搜索商家服务即可开通)
- 根据备注可判断相同价格多人支付（出现相同价格的多并发支付时可要求用户输入随机数字备注解决该问题）
- 两个支付必须分开运行，demo只是作为演示
- 服务器时间必须是国内的时间，不然对不上支付宝微信时间
- 如果使用框架运行可能存在内存溢出问题，可以使用Crontab，请自行去除```ChenPay\Pay::Listen```函数，变量需要另外选择存储方式mysql\redis等

## 更新日志：
#### V1.5
- 新稳定支付宝接口无风控
#### V1.4
- 修复支付宝风控规则更改
#### V1.3
- 修复部分用户无法使用问题
#### V1.2
- 增加判断备注&设置时区
#### V1.1.1
- 应对11月支付宝升级导致账号失效问题
#### V1.0.9
- 支付宝商户订单号改成支付宝交易号
#### V1.0.7
- 10秒超时时间
#### V1.0.6
- 增加支付宝频繁错误码446
#### V1.0.5
- 更新支付宝双接口轮流切换API达到支付宝防止频繁访问阻止机制
- 如果单一接口出现阻止则会持续使用另外接口
