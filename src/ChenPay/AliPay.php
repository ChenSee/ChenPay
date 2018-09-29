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
    /**
     * @param bool $url
     * @param bool $syncKey
     * @return $this
     * @throws PayException
     */
    public function getData($url = false, $syncKey = false)
    {
        // TODO: Implement getData() method.
        try {
            $aliPayHtml = (new \GuzzleHttp\Client())
                ->request('POST', "https://mbillexprod.alipay.com/enterprise/fundAccountDetail.json", ['headers' => [
                    'Accept' => 'application/json, text/javascript',
                    'Accept-Encoding' => 'gzip, deflate, br',
                    'Accept-Language' => 'en-US,en;q=0.9,zh-CN;q=0.8,zh;q=0.7',
                    'Connection' => 'keep-alive',
                    'Content-Length' => '295',
                    'Content-Type' => 'application/x-www-form-urlencoded; charset=UTF-8',
                    'Cookie' => $this->cookie,
                    'Host' => 'mbillexprod.alipay.com',
                    'Origin' => 'https://mbillexprod.alipay.com',
                    'Referer' => 'https://mbillexprod.alipay.com/enterprise/fundAccountDetail.htm',
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/68.0.3440.106 Safari/537.36',
                    'X-Requested-With' => 'XMLHttpRequest'
                ], 'body' => 'queryEntrance=1&billUserId=' . Cookie::getCookieName('uid', $this->cookie) .
                    '&showType=1&type=&precisionQueryKey=tradeNo&' .
                    'startDateInput=' . date('Y-m-d', strtotime('-1 day')) . '+00%3A00%3A00&endDateInput=' . date('Y-m-d') . '+23%3A59%3A59&' .
                    'pageSize=20&pageNum=1&sortTarget=tradeTime&order=descend&sortType=0&' .
                    '_input_charset=gbk&ctoken=' . Cookie::getCookieName('ctoken', $this->cookie)])
                ->getBody();
            $this->html = iconv('GBK', 'UTF-8', $aliPayHtml->getContents());
        } catch (GuzzleException $e) {
            throw new PayException('访问出错', 500);
        } catch (PayException $e) {
            throw new PayException($e->getMessage(), 445);
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
        if (isset($this->json['exception_marking']) || isset($this->json['target']))
            throw new PayException('cookie失效', 445);
        return $this;
    }

    /**
     * 获取最新的订单号
     * @param $fee
     * @param $time
     * @return bool
     */
    public function DataContrast($fee, $time)
    {
        // TODO: Implement DataContrast() method.
        if (isset($this->json['result']['detail']) && is_array($this->json['result']['detail']))
            foreach ($this->json['result']['detail'] as $item)
                if ($item['signProduct'] == '转账收款码' && $item['accountType'] == '交易' &&
                    strtotime($item['tradeTime']) < $time && $item['tradeAmount'] == $fee) {
                    return $item['orderNo'];
                }

        return false;
    }
}