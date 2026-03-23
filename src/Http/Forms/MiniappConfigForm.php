<?php

namespace Ycookies\MiniappManager\Http\Forms;

use Dcat\Admin\Widgets\Form;
use Illuminate\Support\Facades\Crypt;
use Ycookies\MiniappManager\Models\MiniappConfig;

class MiniappConfigForm extends Form
{
    protected string $platform;

    public function __construct(string $platform)
    {
        $this->platform = $platform;

        parent::__construct();
    }

    public function form()
    {
        $labels = $this->platformLabels();
        $config = MiniappConfig::getByPlatform($this->platform);
        $isVerified = $config && $config->is_verified;

        $verifiedBadge = $isVerified
            ? '<span class="badge badge-success">已验证</span>'
            : '<span class="badge badge-warning">未验证</span>';

        $this->html($verifiedBadge, '状态');

        $this->text('app_id', $labels[0])->required();
        $this->password('app_secret', $labels[1])->required();
        $this->text('token', $labels[2]);
        $this->text('encoding_aes_key', $labels[3]);

        $verifyUrl = admin_url('miniapp-manager/' . $this->platform . '/config/verify');
        $this->html(
            '<button type="button" class="btn btn-success btn-verify-config" data-url="' . $verifyUrl . '">验证配置</button>
            <span class="ml-2 text-muted">保存后点击验证，通过平台 API 校验配置是否正确</span>',
            ' '
        );
    }

    public function default()
    {
        $config = MiniappConfig::getByPlatform($this->platform);

        if (!$config) {
            return [];
        }

        return [
            'app_id'           => $config->app_id,
            'app_secret'       => $config->getDecryptedSecret(),
            'token'            => $config->token,
            'encoding_aes_key' => $config->encoding_aes_key,
        ];
    }

    public function handle(array $input)
    {
        $platform = $this->platform;

        if (!isset(MiniappConfig::$platforms[$platform])) {
            return $this->response()->error('不支持的平台类型');
        }

        $appId     = $input['app_id'] ?? '';
        $appSecret = $input['app_secret'] ?? '';

        if (empty($appId) || empty($appSecret)) {
            return $this->response()->error('AppID 和 AppSecret 不能为空');
        }

        $config = MiniappConfig::getByPlatform($platform);

        $saveData = [
            'platform'         => $platform,
            'name'             => MiniappConfig::$platforms[$platform],
            'app_id'           => $appId,
            'app_secret'       => Crypt::encryptString($appSecret),
            'token'            => $input['token'] ?? '',
            'encoding_aes_key' => $input['encoding_aes_key'] ?? '',
            'is_verified'      => 0,
            'is_enabled'       => 0,
        ];

        if ($config) {
            $config->update($saveData);
        } else {
            MiniappConfig::create($saveData);
        }

        return $this->response()->success('配置已保存，请点击「验证配置」按钮验证');
    }

    protected function savedScript()
    {
        return <<<'JS'
if (args.status) {
    Dcat.success(args.message || '保存成功');
}
JS;
    }

    protected function errorScript()
    {
        return <<<'JS'
if (args.message) {
    Dcat.error(args.message);
}
JS;
    }

    protected function platformLabels(): array
    {
        return match ($this->platform) {
            'alipay' => ['AppID', '应用私钥', 'Token（可选）', 'AES密钥（可选）'],
            default  => ['AppID', 'AppSecret', 'Token（消息推送，可选）', 'EncodingAESKey（可选）'],
        };
    }
}
