<?php
/**
 * +----------------------------------------------------------------------
 *   | 草帽支付系统 [ WE CAN DO IT JUST THINK ]
 * +----------------------------------------------------------------------
 *   | Copyright (c) 2018 http://www.iredcap.cn All rights reserved.
 * +----------------------------------------------------------------------
 *   | Licensed ( https://www.apache.org/licenses/LICENSE-2.0 )
 * +----------------------------------------------------------------------
 *   | Author: Brian Waring <BrianWaring98@gmail.com>
 * +----------------------------------------------------------------------
 */

namespace app\api\service;

use app\api\service\payment\WxScan;
use app\api\service\payment\QqScan;
use app\common\library\exception\ParameterException;

/**
 * @method static WxScan wx_scan(array $config) 微信扫码支付
 * @method static QqScan qq_scan(array $config) QQ扫码支付
 */
class ApiPayment
{
    protected $config;

    /**
     * Bootstrap.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     *
     * @author 勇敢的小笨羊 <brianwaring98@gmail.com>
     *
     * @param $method
     * @return mixed
     * @throws \Exception
     */
    public function create($method){
        $payment = __NAMESPACE__ . '\\payment\\' . ucwords(camelize($method));
        if (class_exists($payment)) {
            return new $payment($this->config);
        }
        throw new ParameterException(['msg' => 'Payment [{$method}] Not Exists']);
    }


    /**
     * Magic static call.
     *
     * @author 勇敢的小笨羊 <brianwaring98@gmail.com>
     *
     * @param $method
     * @param $params
     * @return mixed
     * @throws \Exception
     */
    public static function __callStatic($method, $params)
    {
        $app = new self(...$params);

        return $app->create($method);
    }

    /**
     * curl get
     *
     * @author 勇敢的小笨羊 <brianwaring98@gmail.com>
     *
     * @param string $url
     * @param array $options
     * @return mixed
     */
    public static function curlGet($url = '', $options = array())
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        if (!empty($options)) {
            curl_setopt_array($ch, $options);
        }
        //https请求 不验证证书和host
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
    }

    /**
     * curl post
     *
     * @author 勇敢的小笨羊 <brianwaring98@gmail.com>
     *
     * @param string $url
     * @param string $postData
     * @param array $options
     * @return mixed
     */
    public static function curlPost($url = '', $postData = '', $options = array())
    {
        if (is_array($postData)) {
            $postData = http_build_query($postData);
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30); //设置cURL允许执行的最长秒数
        if (!empty($options)) {
            curl_setopt_array($ch, $options);
        }
        //https请求 不验证证书和host
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
    }

    /**
     * 随机字符串
     *
     * @author 勇敢的小笨羊 <brianwaring98@gmail.com>
     *
     * @param int $length
     * @return string
     */
    public static function createNonceStr($length = 16)
    {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $str = '';
        for ($i = 0; $i < $length; $i++) {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        return $str;
    }

    /**
     * 数组转Xml
     *
     * @author 勇敢的小笨羊 <brianwaring98@gmail.com>
     *
     * @param $arr
     * @return string
     */
    public static function arrayToXml($arr)
    {
        $xml = "<xml>";
        foreach ($arr as $key => $val) {
            if (is_numeric($val)) {
                $xml .= "<" . $key . ">" . $val . "</" . $key . ">";
            } else
                $xml .= "<" . $key . "><![CDATA[" . $val . "]]></" . $key . ">";
        }
        $xml .= "</xml>";
        return $xml;
    }

    /**
     * Xml转数组
     *
     * @author 勇敢的小笨羊 <brianwaring98@gmail.com>
     *
     * @param $xml
     * @return mixed
     */
    public static function xmlToArray($xml){

        libxml_disable_entity_loader(true);

        return json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA), JSON_UNESCAPED_UNICODE), true);

    }
}