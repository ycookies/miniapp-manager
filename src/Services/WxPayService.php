<?php
namespace Ycookies\MiniappManager\Services;

use App\Models\Hotel\Setting;
use EasyWeChat\Factory;
use App\Models\Hotel\WxopenMiniProgramOauth;
use App\Models\Hotel\WxopenMiniProgramVersion;

// 微信支付服务
class WxPayService {
    public $payment;
    public $oauthInfo;

    public function __construct() {
        $cache_wxopen = '';//\Cache::get('wxpay_config');
        if(empty($cache_wxopen)){
            $wxpay_config = Setting::getlists([], 'pay_isv');
            \Cache::put('wxpay_config',$wxpay_config);
        }else{
            $wxpay_config = $cache_wxopen;
        }
        $pay_config = [
            'app_id' => $wxpay_config['isv_app_id'], // 微信小程序的app_id 不是公众号ID
            'secret' => $wxpay_config['isv_secret'], // 微信小程序的secret 不是公众号secret
            'mch_id'     =>$wxpay_config['isv_mch_id'], // 小程序绑定微信支付服务商 商户号
            'serial_no' => '2A5F5D68EE387EACB7150C3AAC846DB3D71EB810', // 证书序列号
            'key'        => !empty($wxpay_config['isv_key']) ? $wxpay_config['isv_key']:'', // 小程序绑定微信支付商户号 密钥
            'cert_path'  => !empty($wxpay_config['isv_cert_path']) ? $wxpay_config['isv_cert_path']:'', // XXX: 绝对路径！！！！ // 小程序绑定微信支付商户号 证书
            'key_path'   => !empty($wxpay_config['isv_key_path']) ? $wxpay_config['isv_key_path']:'',      // XXX: 绝对路径！！！！ // 小程序绑定微信支付商户号 证书
            'platform_certs' =>[
                $wxpay_config['isv_platform_pub_key'] => $wxpay_config['isv_platform_cert'],
            ],
            'notify_url' => $wxpay_config['isv_notify_url'],     // 这个小程序支付对应的支付通知地址
            // 下面为可选项
            // 指定 API 调用返回结果的类型：array(default)/collection/object/raw/自定义类名
            'response_type' => 'array',
            'log' => [
                'level' => 'info',
                'file' => storage_path('logs/easywechat-dev.log'),
            ],
        ];

        $payment  = Factory::payment($pay_config);
        //$payment->setHttpClient();
        $this->payment = $payment;
        //return $payment;
    }
    public  function make(){
        return $this->payment;
    }

    // 获取授权账号信息
    public function getAuthorizer($app_id,$hotel_id = ''){
        if(!empty($hotel_id)){
            $oauth        = WxopenMiniProgramOauth::where(['hotel_id' => $hotel_id,'app_type'=>'minapp'])->first();
            $app_id = $oauth->AuthorizerAppid;
        }
        // 获取授权账号信息
        $res = $this->openPlatform->getAuthorizer($app_id);
        return $res;
    }
    // 通过子酒店ID 设置子商户号
    public function setSubMerchant($hotel_id){
        $oauth        = WxopenMiniProgramOauth::where(['hotel_id' => $hotel_id,'app_type' => 'minapp'])->first();
        if(empty($oauth->sub_mch_id)){
            apiReturn('403',0,[],'商家还未开通支付');
        }
        info('设置子商户号'.$hotel_id);
        info([$oauth->sub_mch_id,$oauth->AuthorizerAppid]);

        $this->payment->setSubMerchant($oauth->sub_mch_id,$oauth->AuthorizerAppid);
        return $this->payment;
    }

    // 获取小程序的授权信息
    public function getOauthInfo($app_id,$hotel_id = '',$sub_mch_id = '',$app_type = 'minapp'){
        if(!empty($app_id)){
            $oauth        = WxopenMiniProgramOauth::where(['AuthorizerAppid' => $app_id])->first();
        }
        if(!empty($hotel_id)){
            $oauth        = WxopenMiniProgramOauth::where(['hotel_id' => $hotel_id,'app_type'=>$app_type])->first();
        }
        if(!empty($sub_mch_id)){
            $oauth        = WxopenMiniProgramOauth::where(['sub_mch_id' => $sub_mch_id,'app_type'=>$app_type])->first();
        }
        return $oauth;
    }

    // 生成消费收款小程序码
    public static function makeTradeQrcode($hotel_id,$is_force = false){
        $filenamefull = 'trade-'.$hotel_id.'.png';
        $full_path = public_path('uploads/images').'/'.$filenamefull;
        $qrcode_url = env('APP_URL').'/uploads/images/'.$filenamefull;
        if(!$is_force){
            if(file_exists($full_path)){
                return $qrcode_url;
            }
        }
        $miniProgram = app('wechat.open')->hotelMiniProgram($hotel_id);
        $response = $miniProgram->app_code->getQrCode('/pages2/extend/trade_order');
        if ($response instanceof \EasyWeChat\Kernel\Http\StreamResponse) {
            if(file_exists($full_path)){
                unlink($full_path);
            }
            $filename = $response->saveAs(public_path('uploads/images'), $filenamefull);
            if(!file_exists($full_path)){
                return returnData(204, 0, [], '保存小程序二维码失败');
            }
            $qrcode_url = env('APP_URL').'/uploads/images/'.$filenamefull;
            return $qrcode_url;
        }
        return false;
    }
    // 返回付款码所属厂商
    public static function AuthCodeType($code)
    {
        $alipay = '/^(((2[5-9])|(30))\d{14,22})$/';
        $weixin = '/^((1[0-5])\d{16,16})$/';
        $type   = false;
        if (preg_match($alipay, $code)) {
            $type = 'alipay';
        }
        if (preg_match($weixin, $code)) {
            $type = 'weixin';
        }
        return $type;
    }

    /**
     * 统一支付接口
     * @param string $out_trade_no 商户订单号，需保持唯一性
     * @param int $amount 订单金额
     * @param string $subject 订单标题
     * @param string $openid 用户openid
     * @param int $is_profitsharing 是否需要分账
     * @return array
     */
    public function isvPay($hotel_id, $out_trade_no, $amount,$subject, $openid,$is_profitsharing = 0)
    {
        $isvpay  = app('wechat.isvpay');
        $config  = $isvpay->getOauthInfo('', $hotel_id);
        $app     = $isvpay->setSubMerchant($hotel_id);

        $sys_setting = \App\Models\Hotel\Setting::getlists(['isv_key']);
        if (empty($sys_setting['isv_key'])) {
            return returnData(205, 0, [], '系统支付配置错误,请联系管理员');
        }

        $payinfo = [
            'body'           => $subject,
            'out_trade_no'   => $out_trade_no,
            'total_fee'      => bcmul($amount, 100, 0),
            //'spbill_create_ip' => '123.12.12.123', // 可选，如不传该参数，SDK 将会自动获取相应 IP 地址
            'notify_url'     => env('APP_URL') . '/hotel/notify/wxPayNotify/' . $config->AuthorizerAppid, // 支付结果通知网址，如果不设置则会使用配置里的默认地址
            'trade_type'     => 'JSAPI', // 请对应换成你的支付方式对应的值类型
            'sub_openid'     => $openid,
        ];

        // 是否支持分账
        if (!empty($is_profitsharing)) {
            $payinfo['profit_sharing'] = 'Y';
        }
        $result = $app->order->unify($payinfo);
        addlogs('order_unify', $payinfo, $result);
        if (!empty($result['return_code']) && $result['return_code'] == 'FAIL') {

            return returnData(205, 0, [], $result['return_msg']);
        }

        if (!empty($result['err_code_des'])) {
            return returnData(205, 0, [], $result['err_code_des']);
        }
        if (empty($result['prepay_id'])) {
            return returnData(205, 0, [], '创建预支付订单失败');
        }
        // paySign = MD5(appId=wxd678efh567hg6787&nonceStr=5K8264ILTKCH16CQ2502SI8ZNMTM67VS&package=prepay_id=wx2017033010242291fcfe0db70013231072&signType=MD5&timeStamp=1490840662&key=qazwsxedcrfvtgbyhnujmikolp111111) = 22D9B4E54AB1950F51E0649E8810ACD6
        //                appId=wxf0747582a6796ddf&nonceStr=bWES8fEOD98bWSzp&package=prepay_id=wx31221528868542a2599f3c243867dc0000&signType=MD5&timeStamp=1690812928&key=YKAr9V0IdHl4kvs0CMQ2DTVluSZROlYj
        $timestamp           = (string)time();
        $result['timeStamp'] = $timestamp;
        $sign                = 'appId=' . $config->AuthorizerAppid . '&nonceStr=' . $result['nonce_str'] . '&package=prepay_id=' . $result['prepay_id'] . '&signType=MD5&timeStamp=' . $timestamp . '&key=' . $sys_setting['isv_key'];

        $result['paySign']   = strtoupper(MD5($sign));
        $result['nonceStr']  = $result['nonce_str'];
        $result['package']   = 'prepay_id=' . $result['prepay_id'];
        //$result['open_appid'] = 'wx662e8c427b24bdbe';
        $result['sub_mch_id'] = $config->sub_mch_id;
        //$result['signType'] = 'HMAC-SHA256';
        return returnData(200, 1, ['pay_amount' => $amount, 'pay_data' => $result, 'out_trade_no' => $out_trade_no], 'ok');
    }

}