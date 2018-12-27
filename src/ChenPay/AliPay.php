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
                        'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/70.0.3538.102 Safari/537.36',
                        'Content-Type' => 'application/x-www-form-urlencoded; charset=UTF-8',
                        'Accept' => 'application/json, text/javascript',
                        'Referer' => 'https://mbillexprod.alipay.com/enterprise/tradeListQuery.htm',
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
     * @return \Psr\Http\Message\StreamInterface
     * @throws PayException
     */
    public function HtmlOne()
    {
        try {
            return (new \GuzzleHttp\Client())
                ->request('POST', "https://mbillexprod.alipay.com/enterprise/tradeListQuery.json", [
                    'timeout' => 10,
                    'headers' => [
                        'Cookie' => $this->cookie,
                        'Origin' => 'https://mbillexprod.alipay.com',
                        'Accept-Encoding' => 'gzip, deflate, br',
                        'Accept-Language' => 'zh-CN,zh;q=0.9,en-US;q=0.8,en;q=0.7',
                        'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/68.0.3440.106 Safari/537.36',
                        'Content-Type' => 'application/x-www-form-urlencoded; charset=UTF-8',
                        'Accept' => 'application/json, text/javascript',
                        'Referer' => 'https://mbillexprod.alipay.com/enterprise/tradeListQuery.htm',
                        'X-Requested-With' => 'XMLHttpRequest',
                        'Connection' => 'keep-alive',
                    ],
                    'body' => 'queryEntrance=1&billUserId=' . Cookie::getCookieName('uid', $this->cookie) .
                        '&status=SUCCESS&entityFilterType=0&activeTargetSearchItem=tradeNo&tradeFrom=ALL&startTime=' .
                        date('Y-m-d', strtotime('-1 day')) . '+00%3A00%3A00&endTime=' . date('Y-m-d') .
                        '+23%3A59%3A59&pageSize=20&pageNum=1&total=1&sortTarget=gmtCreate&order=descend&sortType=0&_input_charset=gbk&ctoken=' .
                        Cookie::getCookieName('ctoken', $this->cookie),
                ])
                ->getBody();
        } catch (GuzzleException $e) {
            throw new PayException($e->getMessage(), 500);
        } catch (PayException $e) {
            throw new PayException($e->getMessage(), 445);
        }
    }

    /**
     * @return \Psr\Http\Message\StreamInterface
     * @throws PayException
     */
    public function HtmlTwo()
    {
        try {
            return (new \GuzzleHttp\Client())
                ->request('POST', "https://mbillexprod.alipay.com/enterprise/fundAccountDetail.json", [
                    'timeout' => 10,
                    'headers' => [
                        'Cookie' => $this->cookie,
                        'Origin' => 'https://mbillexprod.alipay.com',
                        'Accept-Encoding' => 'gzip, deflate, br',
                        'Accept-Language' => 'zh-CN,zh;q=0.9,en-US;q=0.8,en;q=0.7',
                        'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/68.0.3440.106 Safari/537.36',
                        'Content-Type' => 'application/x-www-form-urlencoded; charset=UTF-8',
                        'Accept' => 'application/json, text/javascript',
                        'Referer' => 'https://mbillexprod.alipay.com/enterprise/fundAccountDetail.htm',
                        'X-Requested-With' => 'XMLHttpRequest',
                        'Connection' => 'keep-alive',
                    ],

                    'body' => 'queryEntrance=1&billUserId=' . Cookie::getCookieName('uid', $this->cookie) .
                        '&showType=1&type=&precisionQueryKey=tradeNo&' .
                        'startDateInput=' . date('Y-m-d', strtotime('-1 day')) . '+00%3A00%3A00&endDateInput=' . date('Y-m-d') . '+23%3A59%3A59&' .
                        'pageSize=20&pageNum=1&total=1&sortTarget=tradeTime&order=descend&sortType=0&' .
                        '_input_charset=gbk&ctoken=' . Cookie::getCookieName('ctoken', $this->cookie)
                ])
                ->getBody();
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
        $this->url = $url;
        $this->getRefresh();
        $aliPayHtml = $url ? $this->HtmlOne()->getContents() : $this->HtmlTwo()->getContents();
        if (preg_match('/"failed"/', $aliPayHtml)) {
            $aliPayHtml = !$url ? $this->HtmlOne()->getContents() : $this->HtmlTwo()->getContents();
            if (preg_match('/"failed"/', $aliPayHtml)) throw new PayException('频繁访问', 446);
            $this->url = !$url;
        }
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
     */
    public function DataContrast($fee, $time, $Minute = 3, $Remarks = false)
    {
        // TODO: Implement DataContrast() method.
//        print_r($this->json['result']['detail']);
        if (isset($this->json['result']['detail']) && is_array($this->json['result']['detail']))
            foreach ($this->json['result']['detail'] as $item) {
                if ((
                        ($this->url && $item['tradeFrom'] == '外部商户' && $item['direction'] == '卖出' &&
                            strtotime($item['gmtCreate']) > $time - $Minute * 60 && strtotime($item['gmtCreate']) < $time &&
                            $item['totalAmount'] == $fee) ||
                        (!$this->url && $item['signProduct'] == '转账收款码' && $item['accountType'] == '交易' &&
                            strtotime($item['tradeTime']) > $time - $Minute * 60 && strtotime($item['tradeTime']) < $time &&
                            $item['tradeAmount'] == $fee)
                    ) && ($Remarks === false || ($Remarks != '' && (preg_match("/{$Remarks}/", $item['goodsTitle'])) ||
                            ($Remarks == '' && $item['goodsTitle'] == '商品')))) {
                    return $item['tradeNo'];
                }
            }

        return false;
    }
}