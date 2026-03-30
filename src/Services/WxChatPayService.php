<?php

namespace Ycookies\MiniappManager\Services;

use App\Models\Hotel\Users as UsersModel;
use App\Models\Hotel\UsersInfo as UsersInfoModel;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use EasyWeChat\Factory;
use WeChatPay\Builder;
use WeChatPay\Crypto\Rsa;
use WeChatPay\Util\PemUtil;

/**
 * 微信支付服务件
 * @package App\Services
 * anthor Fox
 */
class WxChatPayService extends BaseService {
    /**
     * @desc 微信支付点金计划
     * @return \WeChatPay\BuilderChainable
     * author eRic
     * dateTime 2025-04-04 22:22
     */
    public function make2(){
        $config = config('wechat.min2');

        // 商户号
        $merchantId = $config['mch_id'];
        // 从本地文件中加载「商户API私钥」，「商户API私钥」会用来生成请求的签名
        $merchantPrivateKeyFilePath = 'file://' . $config['key_path'];
        $merchantPrivateKeyInstance = Rsa::from($merchantPrivateKeyFilePath, Rsa::KEY_TYPE_PRIVATE);

        // 「商户API证书」的「证书序列号」
        $merchantCertificateSerial = $config['serial_no'];

        // 从本地文件中加载「微信支付平台证书」，用来验证微信支付应答的签名
        /*        $platformCertificateFilePath = 'file://' . $config['platform_cert_path'];
                $platformPublicKeyInstance   = Rsa::from($platformCertificateFilePath, Rsa::KEY_TYPE_PUBLIC);*/

        // 加载平台公钥实例
        /*$platformCertificateFilePath = 'file://' . $config['platform_pub_cert']; // 证书文件路径
        $platformPublicKeyInstance = Rsa::from($platformCertificateFilePath, Rsa::KEY_TYPE_PUBLIC);*/


        // 从「微信支付平台证书」中获取「证书序列号」
        //$platformCertificateSerial = PemUtil::parseCertificateSerialNo($platformCertificateFilePath);

        // 从本地文件中加载「微信支付公钥」，用来验证微信支付应答的签名
        $platformPublicKeyFilePath    = 'file://' . $config['platform_pub_cert'];
        $twoPlatformPublicKeyInstance = Rsa::from($platformPublicKeyFilePath, Rsa::KEY_TYPE_PUBLIC);

        // 「微信支付公钥」的「微信支付公钥ID」
        // 需要在 商户平台 -> 账户中心 -> API安全 查询
        $platformPublicKeyId = $config['platform_pub_id'];
        // 构造一个 APIv3 客户端实例
        $instance = Builder::factory([
            'mchid'      => $merchantId,
            'serial'     => $merchantCertificateSerial,
            'privateKey' => $merchantPrivateKeyInstance,
            'certs'      => [
                $platformPublicKeyId => $twoPlatformPublicKeyInstance,
            ],
        ]);

        return $instance;
    }

    public function make_back(){
        $config = config('wechat.min2');

        // 商户号
        $merchantId = $config['mch_id'];
        // 从本地文件中加载「商户API私钥」，「商户API私钥」会用来生成请求的签名
        $merchantPrivateKeyFilePath = 'file://' . $config['key_path'];
        $merchantPrivateKeyInstance = Rsa::from($merchantPrivateKeyFilePath, Rsa::KEY_TYPE_PRIVATE);

        // 「商户API证书」的「证书序列号」
        $merchantCertificateSerial = $config['serial_no'];

        // 从本地文件中加载「微信支付平台证书」，用来验证微信支付应答的签名
        $platformCertificateFilePath = 'file://' . $config['platform_cert_path'];
        $platformPublicKeyInstance   = Rsa::from($platformCertificateFilePath, Rsa::KEY_TYPE_PUBLIC);

        // 从「微信支付平台证书」中获取「证书序列号」
        $platformCertificateSerial = PemUtil::parseCertificateSerialNo($platformCertificateFilePath);

        // 构造一个 APIv3 客户端实例
        $instance = Builder::factory([
            'mchid'      => $merchantId,
            'serial'     => $merchantCertificateSerial,
            'privateKey' => $merchantPrivateKeyInstance,
            'certs'      => [
                $platformCertificateSerial => $platformPublicKeyInstance,
            ],
        ]);

        return $instance;

        // 发送请求
        /*$resp = $instance->chain('v3/certificates')->get(
            ['debug' => false] // 调试模式
        );*/

        // 开启 特约商户号 点金计划
        /*$resp = $instance->chain('/v3/goldplan/merchants/changegoldplanstatus')->post([
            'json' => [
                'sub_mchid'           => $this->config['sub_mch_id'],
                'operation_type'      => 'OPEN',
                'operation_pay_scene' => 'JSAPI_AND_MINIPROGRAM'
            ]
        ]);*/

        /*// 开启或关闭 特约商户号 商家小票功能
        $resp = $instance->chain('/v3/goldplan/merchants/changecustompagestatus')->post([
            'json' => [
                'sub_mchid'           => $this->config['sub_mch_id'],
                'operation_type'      => 'OPEN',
            ]
        ]);

        // 同业过滤标签管理
        $resp = $instance->chain('/v3/goldplan/merchants/set-advertising-industry-filter')->post([
            'json' => [
                'sub_mchid'           => $this->config['sub_mch_id'],
                'advertising_industry_filters'      => ['TOURISM','SPORT','SERVICES'], //同业过滤标签最少传一个  最多三个
            ]
        ]);*/


        // 开通广告展示
        /*$resp = $instance->chain('/v3/goldplan/merchants/open-advertising-show')->post([
            'json' => [
                'sub_mchid'           => $this->config['sub_mch_id'],
            ]
        ]);*/

        // 关闭广告展示
        /*$resp = $instance->chain('/v3/goldplan/merchants/close-advertising-show')->post([
            'json' => [
                'sub_mchid'           => $this->config['sub_mch_id'],
            ]
        ]);
        $res = $resp->getBody()->getContents();*/
        /*可以正常使用*/
    }
}