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

class AliPay extends Pay
{
    public $url = false;

    /**
     * @return $this
     * @throws PayException
     */
    public function getRefresh()
    {
        try {
            $aliPayHtml = (new \GuzzleHttp\Client())
                ->request('POST', "https://enterpriseportal.alipay.com/portal/navload.json?t=" . time() * 1000, [
                    'timeout' => 10,
                    'headers' => [
                        'Cookie' => $this->cookie,
                        'Accept-Encoding' => 'gzip, deflate, br',
                        'Accept-Language' => 'zh-CN,zh;q=0.9,en-US;q=0.8,en;q=0.7',
                        'User-Agent' => 'Mozilla/5.0 (Linux; U; Android 9; zh-CN; MI MAX 3 Build/PKQ1.190223.001) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/57.0.2987.108 UCBrowser/11.8.8.968 UWS/2.13.2.91 Mobile Safari/537.36 UCBS/2.13.2.91_190617211143 NebulaSDK/1.8.100112 Nebula AlipayDefined(nt:4G,ws:393|0|2.75) AliApp(AP/10.1.68.7434) AlipayClient/10.1.68.7434 Language/zh-Hans useStatusBar/true isConcaveScreen/false Region/CN',
                        'Content-Type' => 'application/x-www-form-urlencoded; charset=UTF-8',
                        'Accept' => 'application/json, text/javascript',
                        'Referer' => 'https://mrchportalweb.alipay.com/user/home.htm',
                        'Origin' => 'https://mbillexprod.alipay.com',
                        'Connection' => 'keep-alive',
                    ],
                    'body' => 'action=loadEntInfo'
                ])
                ->getBody();
        } catch (GuzzleException $e) {
            throw new PayException($e->getMessage(), 500);
        }
        if (!preg_match('/navResult/', $aliPayHtml)) throw new PayException('cookie失效', 445);
        return $this;
    }

    /**
     * @param $order_sn
     * @return
     * @throws PayException
     */
    public function getOrderRemark($order_sn)
    {
        try {
            $html = (new \GuzzleHttp\Client())
                ->request('GET', "https://tradeeportlet.alipay.com/wireless/tradeDetail.htm?tradeNo=" . $order_sn, [
                    'timeout' => 10,
                    'headers' => [
                        'Cookie' => $this->cookie,
                        'Sec-Fetch-Mode' => 'no-cors',
                        'Referer' => 'https://render.alipay.com/p/z/merchant-mgnt/simple-order.html',
                        'User-Agent' => 'Mozilla/5.0 (Linux; U; Android 9; zh-CN; MI MAX 3 Build/PKQ1.190223.001) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/57.0.2987.108 UCBrowser/11.8.8.968 UWS/2.13.2.91 Mobile Safari/537.36 UCBS/2.13.2.91_190617211143 NebulaSDK/1.8.100112 Nebula AlipayDefined(nt:4G,ws:393|0|2.75) AliApp(AP/10.1.68.7434) AlipayClient/10.1.68.7434 Language/zh-Hans useStatusBar/true isConcaveScreen/false Region/CN'
                    ]
                ])
                ->getBody()->getContents();
            $html = iconv('GBK', 'UTF-8', $html);
            preg_match_all('/trade-detail-info">.*?class="trade-info-value">(.*?)<\/div/ius', $html, $all);
            return isset($all[1][0]) ? trim($all[1][0]) : '';
        } catch (GuzzleException $e) {
            throw new PayException($e->getMessage(), 500);
        } catch (\Exception $e) {
            throw new PayException('处理出错', 444);
        }
    }

    /**
     * @return \Psr\Http\Message\StreamInterface
     * @throws PayException
     */
    public function aliHtml()
    {
        try {
            return (new \GuzzleHttp\Client())
                ->request('GET', "https://mbillexprod.alipay.com/enterprise/walletTradeList.json?lastTradeNo=&lastDate=" .
                    "&pageSize=20&shopId=&pageNum=1&_input_charset=utf-8&ctoken" .
                    "&source=&_ksTS=" . (time() * 1000) . "_29", [
                    'timeout' => 10,
                    'headers' => [
                        'Cookie' => $this->cookie,
                        'Sec-Fetch-Mode' => 'no-cors',
                        'Referer' => 'https://render.alipay.com/p/z/merchant-mgnt/simple-order.html',
                        'User-Agent' => 'Mozilla/5.0 (Linux; U; Android 9; zh-CN; MI MAX 3 Build/PKQ1.190223.001) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/57.0.2987.108 UCBrowser/11.8.8.968 UWS/2.13.2.91 Mobile Safari/537.36 UCBS/2.13.2.91_190617211143 NebulaSDK/1.8.100112 Nebula AlipayDefined(nt:4G,ws:393|0|2.75) AliApp(AP/10.1.68.7434) AlipayClient/10.1.68.7434 Language/zh-Hans useStatusBar/true isConcaveScreen/false Region/CN'
                    ]
                ]);
        } catch (GuzzleException $e) {
            throw new PayException($e->getMessage(), 500);
        } catch (PayException $e) {
            throw new PayException($e->getMessage(), 445);
        }
    }

    /**
     * @param bool $url
     * @param bool $syncKey
     * @return $this
     * @throws PayException
     */
    public function getData($url = false, $syncKey = false)
    {
        // TODO: Implement getData() method.
        $this->getRefresh();
        $aliPayHtml = $this->aliHtml()->getBody()->getContents();
        try {
            $this->html = iconv('GBK', 'UTF-8', $aliPayHtml);
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
//        if (isset($this->json['exception_marking'])) throw new PayException('数据出错', 444);
        if (isset($this->json['target'])) throw new PayException('cookie失效', 445);
        return $this;
    }

    /**
     * 获取最新的订单号
     * @param $fee
     * @param $time
     * @param int $Minute
     * @param bool $Remarks
     * @return array|bool
     * @throws PayException
     */
    public function DataContrast($fee, $time, $Minute = 3, $Remarks = false)
    {
        // TODO: Implement DataContrast() method.
//        print_r($this->json['result']['list'][0]);
        if (isset($this->json['result']['list']) && is_array($this->json['result']['list']))
            foreach ($this->json['result']['list'] as $item) {
                if ((strtotime($item['dateKey']) > $time - $Minute * 60 && strtotime($item['dateKey']) < $time &&
                    $item['tradeTransAmount'] == $fee)) {
                    $remark = $this->getOrderRemark($item['tradeNo']);
                    if ($Remarks === false || ($Remarks != '' && $remark == $Remarks) || ($Remarks == '' && $remark == '商品')) {
                        return $item['tradeNo'];
                    }
                }
            }

        return false;
    }
}