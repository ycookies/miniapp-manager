<?php

namespace Ycookies\MiniappManager\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Ycookies\MiniappManager\Models\MiniappConfig;

/**
 * 检查指定平台配置是否已验证，未验证则跳转到配置页
 */
class CheckPlatformConfig
{
    public function handle(Request $request, Closure $next, string $platform)
    {
        $config = MiniappConfig::getVerifiedConfig($platform);

        if (!$config) {
            $configUrl = admin_url('miniapp-manager/' . $platform . '/config');

            if ($request->expectsJson()) {
                return response()->json([
                    'code'    => 403,
                    'msg'     => '请先完成' . (MiniappConfig::$platforms[$platform] ?? $platform) . '配置并验证',
                    'redirect' => $configUrl,
                ], 403);
            }

            return redirect($configUrl);
        }

        return $next($request);
    }
}
