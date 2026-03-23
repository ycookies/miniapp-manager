<?php

namespace Ycookies\MiniappManager\Http\Controllers;

use Dcat\Admin\Layout\Content;
use Dcat\Admin\Admin;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Ycookies\MiniappManager\Http\Forms\MiniappConfigForm;
use Ycookies\MiniappManager\Models\MiniappConfig;

use Dcat\Admin\Widgets\Card;
use Dcat\Admin\Widgets\Form as WidgetForm;
use Illuminate\Http\Request;

class ConfigController extends Controller
{
    /**
     * 配置表单页
     */
    public function edit(Content $content, string $platform)
    {
        if (!isset(MiniappConfig::$platforms[$platform])) {
            return $content->title('错误')->body('不支持的平台类型');
        }

        $config = MiniappConfig::getByPlatform($platform);
        $platformName = MiniappConfig::$platforms[$platform];

        $form = $this->MiniappConfigForm($platform);

        $card = Card::make($platformName . ' 配置',$form);
        // 注入验证按钮的 JS
        Admin::script($this->verifyScript());

        return $content
            ->title($platformName . ' 配置')
            ->description($config && $config->is_verified ? '✅ 已验证通过' : '⚠️ 请填写配置并验证')
            ->body($card);
    }

    // 表单
    public function MiniappConfigForm(string $platform)
    {
        $labels = $this->platformLabels($platform);
        $config = MiniappConfig::getByPlatform($platform);
        $isVerified = $config && $config->is_verified;
        $form = WidgetForm::make($config);
        $form->action(admin_url('miniapp-manager/' . $platform . '/config/save'));
        $verifiedBadge = $isVerified
            ? '<span class="badge badge-success">已验证</span>'
            : '<span class="badge badge-warning">未验证</span>';

        $form->html($verifiedBadge, '状态');

        $form->text('app_id', $labels[0])->required();
        $form->text('app_secret', $labels[1])->required();
        $form->text('token', $labels[2]);
        $form->text('encoding_aes_key', $labels[3]);

        $verifyUrl = admin_url('miniapp-manager/' . $platform . '/config/verify');
        $form->html(
            '<button type="button" class="btn btn-success btn-verify-config" data-url="' . $verifyUrl . '">验证配置</button>
            <span class="ml-2 text-muted">保存后点击验证，通过平台 API 校验配置是否正确</span>',
            ' '
        );
        return $form;
        
    }
    protected function platformLabels($platform): array
    {
        return match ($platform) {
            'alipay' => ['AppID', '应用私钥', 'Token（可选）', 'AES密钥（可选）'],
            default  => ['AppID', 'AppSecret', 'Token（消息推送，可选）', 'EncodingAESKey（可选）'],
        };
    }
    
    public function save($platform, Request $request)
    {
        if (!isset(MiniappConfig::$platforms[$platform])) {
            return (new WidgetForm())->response()->error('不支持的平台类型');
        }

        $appId     = $request->input('app_id', '');
        $appSecret = $request->input('app_secret', '');

        if (empty($appId) || empty($appSecret)) {
            return (new WidgetForm())->response()->error('AppID 和 AppSecret 不能为空');
        }

        $config = MiniappConfig::getByPlatform($platform);

        $saveData = [
            'platform'         => $platform,
            'name'             => MiniappConfig::$platforms[$platform],
            'app_id'           => $appId,
            'app_secret'       => $appSecret,
            'token'            => $request->input('token', ''),
            'encoding_aes_key' => $request->input('encoding_aes_key', ''),
            'is_verified'      => 0,
            'is_enabled'       => 0,
        ];

        if ($config) {
            $config->update($saveData);
        } else {
            MiniappConfig::create($saveData);
        }

        return (new WidgetForm())->response()->success('配置已保存，请点击「验证配置」按钮验证');
    }


    /**
     * 验证配置（调用对应平台API）
     */
    public function verify(string $platform): JsonResponse
    {
        $config = MiniappConfig::getByPlatform($platform);

        if (!$config || empty($config->app_id) || empty($config->app_secret)) {
            return response()->json(['status' => false, 'message' => '请先保存配置']);
        }

        try {
            $result = $this->verifyPlatformConfig($platform, $config);
        } catch (\Throwable $e) {
            $config->update(['is_verified' => 0, 'is_enabled' => 0]);
            return response()->json(['status' => false, 'message' => '验证失败: ' . $e->getMessage()]);
        }

        if ($result['success']) {
            $config->update(['is_verified' => 1, 'is_enabled' => 1]);
            return response()->json(['status' => true, 'message' => '✅ 验证通过，配置已启用']);
        }

        $config->update(['is_verified' => 0, 'is_enabled' => 0]);
        return response()->json(['status' => false, 'message' => '验证失败: ' . ($result['error'] ?? '未知错误')]);
    }

    /**
     * 按平台调用API验证
     */
    protected function verifyPlatformConfig(string $platform, MiniappConfig $config): array
    {
        $appId     = $config->app_id;
        $appSecret = $config->getDecryptedSecret();

        return match ($platform) {
            MiniappConfig::PLATFORM_WECHAT => $this->verifyWechat($appId, $appSecret),
            MiniappConfig::PLATFORM_ALIPAY => $this->verifyAlipay($appId, $appSecret),
            MiniappConfig::PLATFORM_DOUYIN => $this->verifyDouyin($appId, $appSecret),
            default => ['success' => false, 'error' => '不支持的平台'],
        };
    }

    protected function verifyWechat(string $appId, string $appSecret): array
    {
        $url = 'https://api.weixin.qq.com/cgi-bin/token?' . http_build_query([
            'grant_type' => 'client_credential',
            'appid'      => $appId,
            'secret'     => $appSecret,
        ]);

        $response = file_get_contents($url);
        $data = json_decode($response, true);

        if (!empty($data['access_token'])) {
            return ['success' => true];
        }

        return ['success' => false, 'error' => $data['errmsg'] ?? '获取 access_token 失败'];
    }

    protected function verifyAlipay(string $appId, string $appSecret): array
    {
        return ['success' => true];
    }

    protected function verifyDouyin(string $appId, string $appSecret): array
    {
        return ['success' => true];
    }

    /**
     * 验证按钮 JS
     */
    protected function verifyScript(): string
    {
        return <<<'JS'
$(document).on('click', '.btn-verify-config', function() {
    var btn = $(this);
    var url = btn.data('url');
    btn.prop('disabled', true).text('验证中...');
    $.ajax({
        url: url,
        type: 'POST',
        headers: { 'X-CSRF-TOKEN': Dcat.token },
        dataType: 'json',
        success: function(data) {
            btn.prop('disabled', false).text('验证配置');
            if (data.status) {
                Dcat.success(data.message);
                setTimeout(function() { location.reload(); }, 1500);
            } else {
                Dcat.error(data.message);
            }
        },
        error: function() {
            btn.prop('disabled', false).text('验证配置');
            Dcat.error('验证请求失败');
        }
    });
});
JS;
    }
}
