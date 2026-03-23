<?php

namespace Ycookies\MiniappManager\Services;

use EasyWeChat\MiniApp\Application;
use Ycookies\MiniappManager\Models\MiniappConfig;

class WechatMiniappService
{
    protected Application $app;

    public function __construct()
    {
        $configRecord = MiniappConfig::getVerifiedConfig(MiniappConfig::PLATFORM_WECHAT);

        if (!$configRecord) {
            throw new \RuntimeException('微信小程序配置未完成或未验证，请先在后台完成配置');
        }

        $config = [
            'app_id'  => $configRecord->app_id,
            'secret'  => $configRecord->getDecryptedSecret(),
            'token'   => $configRecord->token ?: '',
            'aes_key' => $configRecord->encoding_aes_key ?: '',
        ];

        $this->app = new Application($config);
    }

    /**
     * code 换取 openid + session_key
     */
    public function codeToSession(string $code): array
    {
        $utils = $this->app->getUtils();

        return $utils->codeToSession($code);
    }

    /**
     * 获取手机号 (新版 code 方式)
     */
    public function getPhoneNumber(string $code): array
    {
        $response = $this->app->getClient()->postJson('/wxa/business/getuserphonenumber', [
            'code' => $code,
        ]);

        return $response->toArray();
    }

    /**
     * 生成小程序码 (无数量限制)
     */
    public function getUnlimitedQrcode(string $scene, string $page = '', int $width = 430): string
    {
        $params = [
            'scene' => $scene,
            'width' => $width,
        ];

        if ($page !== '') {
            $params['page'] = $page;
        }

        $response = $this->app->getClient()->postJson('/wxa/getwxacodeunlimit', $params);

        // 返回二进制图片内容
        return $response->getContent();
    }

    /**
     * 发送订阅消息
     */
    public function sendSubscribeMessage(string $openid, string $templateId, array $data, string $page = ''): array
    {
        $params = [
            'touser'      => $openid,
            'template_id' => $templateId,
            'data'        => $data,
        ];

        if ($page !== '') {
            $params['page'] = $page;
        }

        $response = $this->app->getClient()->postJson('/cgi-bin/message/subscribe/send', $params);

        return $response->toArray();
    }

    /**
     * 获取 EasyWeChat Application 实例
     */
    public function getApp(): Application
    {
        return $this->app;
    }
}
