<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/9/28 0028
 * Time: 下午 16:35
 */

namespace ChenPay;

use ChenPay\PayException\PayException;
use \GuzzleHttp\Exception\GuzzleException;

class WxPay extends Pay
{
    public $url = false;
    public $syncKey = false;

    /**
     * 微信心跳包
     * @return $this
     * @throws PayException
     */
    public function getSyncKey()
    {
        try {
            $html = (new \GuzzleHttp\Client())
                ->request('POST', "https://" . $this->url . "/cgi-bin/mmwebwx-bin/webwxinit?r=695888609", [
                    'timeout' => 10,
                    'headers' => [
                        'Accept' => 'application/json, text/javascript',
                        'Accept-Encoding' => 'gzip, deflate, br',
                        'Accept-Language' => 'en-US,en;q=0.9,zh-CN;q=0.8,zh;q=0.7',
                        'Connection' => 'keep-alive',
                        'Content-Length' => '295',
                        'Content-Type' => 'application/json;charset=UTF-8',
                        'Cookie' => $this->cookie,
                        'Host' => $this->url,
                        'Origin' => 'https://' . $this->url,
                        'Referer' => 'https://' . $this->url . '/',
                        'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/68.0.3440.106 Safari/537.36'
                    ],
                    'body' => '{"BaseRequest":{"Uin":' . Cookie::getCookieName('wxuin', $this->cookie) .
                        ',"Sid":"' . Cookie::getCookieName('wxsid', $this->cookie) . '","Skey":' .
                        '"","DeviceID":"e453731506754000"}}'
                ])
                ->getBody();
            return json_decode($html->getContents(), true);
        } catch (GuzzleException $e) {
            throw new PayException('访问出错', 500);
        } catch (PayException $e) {
            throw new PayException($e->getMessage(), $e->getCode());
        } catch (\Exception $e) {
            throw new PayException('处理出错', 444);
        }
    }

    /**
     * @param $url
     * @param $syncKey
     * @return $this
     * @throws PayException
     */
    public function getData($url, $syncKey = false)
    {
        // TODO: Implement getData() method.
        $this->url = $url;
        if (!$syncKey || preg_match('/"Count"\:0/', $syncKey)) {
            $syncJson = $this->getSyncKey();
            if ($syncJson['BaseResponse']['Ret'] > 0) throw new PayException('cookie失效', 445);
            $sync = json_encode($syncJson['SyncKey']);
        } else $sync = $syncKey;
        try {
            $html = (new \GuzzleHttp\Client())
                ->request('POST', "https://" . $this->url . "/cgi-bin/mmwebwx-bin/webwxsync?sid=" .
                    Cookie::getCookieName('wxsid', $this->cookie) . "&skey=", [
                    'timeout' => 10,
                    'headers' => [
                        'Accept' => 'application/json, text/javascript',
                        'Accept-Encoding' => 'gzip, deflate, br',
                        'Accept-Language' => 'en-US,en;q=0.9,zh-CN;q=0.8,zh;q=0.7',
                        'Connection' => 'keep-alive',
                        'Content-Length' => '295',
                        'Content-Type' => 'application/json;charset=UTF-8',
                        'Cookie' => $this->cookie,
                        'Host' => $this->url,
                        'Origin' => 'https://' . $this->url,
                        'Referer' => 'https://' . $this->url . '/',
                        'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/68.0.3440.106 Safari/537.36'
                    ],
                    'body' => '{"BaseRequest":{"Uin":' . Cookie::getCookieName('wxuin', $this->cookie) .
                        ',"Sid":"' . Cookie::getCookieName('wxsid', $this->cookie) . '","Skey":"' .
                        '","DeviceID":"e453731506754000"},"SyncKey":' . $sync .
                        ',"rr":' . rand(100000000, 999999999) . '}'
                ])
                ->getBody();
            $this->html = $html->getContents();
            $this->syncKey = json_encode(json_decode($this->html, true)['SyncKey']);
        } catch (GuzzleException $e) {
            throw new PayException($e->getMessage(), 500);
        } catch (PayException $e) {
            throw new PayException($e->getMessage(), $e->getCode());
        } catch (\Exception $e) {
            throw new PayException('处理出错', 444);
        }
        return $this;
    }

    /**
     * @return $this
     * @throws PayException
     */
    public function DataHandle()
    {
        try {
            $this->json = json_decode($this->html, true);
        } catch (\Exception $e) {
            throw new PayException('解析出错', 444);
        }
        if ($this->json['BaseResponse']['Ret'] > 0)
            throw new PayException('cookie失效', 445);
        return $this;
    }

    /**
     * 获取最新的订单号
     * @param $fee
     * @param $time
     * @param int $Minute
     * @param bool $Remarks
     * @return array|bool
     */
    public function DataContrast($fee, $time, $Minute = 3, $Remarks = false)
    {
        // TODO: Implement DataContrast() method.
        if (isset($this->json['AddMsgList']) && is_array($this->json['AddMsgList']))
            foreach ($this->json['AddMsgList'] as $item) {
                if (preg_match('/微信支付收款/', $item['FileName'])) {
                    $fees = explode('微信支付收款', $item['FileName']);
                    $fees = explode('元', $fees[1])[0];
                    if ($item['CreateTime'] < $time && $item['CreateTime'] > $time - $Minute * 60 &&
                        $fees == $fee && ($Remarks === false || (($Remarks != '' && preg_match("/备注：{$Remarks}</", $item['Content']))
                                || ($Remarks == '' && !preg_match("/备注：/", $item['Content'])))
                        )) {
                        return $item['MsgId'];
                    }
                }
            }
        return false;
    }
}