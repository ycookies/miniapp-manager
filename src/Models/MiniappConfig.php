<?php

namespace Ycookies\MiniappManager\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class MiniappConfig extends Model
{
    use HasDateTimeFormatter;

    protected $table = 'miniapp_configs';

    protected $guarded = [];

    // 平台常量
    const PLATFORM_WECHAT = 'wechat';
    const PLATFORM_ALIPAY = 'alipay';
    const PLATFORM_DOUYIN = 'douyin';

    public static array $platforms = [
        self::PLATFORM_WECHAT => '微信小程序',
        self::PLATFORM_ALIPAY => '支付宝小程序',
        self::PLATFORM_DOUYIN => '抖音小程序',
    ];

    /**
     * 获取解密后的 AppSecret
     */
    public function getDecryptedSecret(): string
    {
        if (empty($this->app_secret)) {
            return '';
        }

        try {
            return Crypt::decryptString($this->app_secret);
        } catch (\Throwable $e) {
            return $this->app_secret;
        }
    }

    /**
     * 按平台获取已验证启用的配置
     */
    public static function getVerifiedConfig(string $platform): ?self
    {
        return static::where('platform', $platform)
            ->where('is_verified', 1)
            ->where('is_enabled', 1)
            ->first();
    }

    /**
     * 按平台获取配置（不论状态）
     */
    public static function getByPlatform(string $platform): ?self
    {
        return static::where('platform', $platform)->first();
    }
}
