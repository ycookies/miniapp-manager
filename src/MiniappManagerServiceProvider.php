<?php

namespace Ycookies\MiniappManager;

use Dcat\Admin\Extend\ServiceProvider;
use Dcat\Admin\Admin;
use Illuminate\Support\Facades\Route;
use Ycookies\MiniappManager\Http\Middleware\CheckPlatformConfig;

class MiniappManagerServiceProvider extends ServiceProvider
{
	protected $js = [
        'js/index.js',
    ];
	protected $css = [
		'css/index.css',
	];

    protected $middleware = [];

    protected $exceptRoutes = [
        'auth' => [
            'member-api/miniapp/*',
        ],
    ];

    protected $menu = [
        [
            'title' => '小程序管理',
            'uri'   => '',
            'icon'  => 'feather icon-smartphone',
        ],
        // 微信小程序
        [
            'parent' => '小程序管理',
            'title'  => '微信小程序',
            'uri'    => '',
            'icon'   => 'feather icon-message-circle',
        ],
        [
            'parent' => '微信小程序',
            'title'  => '平台配置',
            'icon'   => 'feather icon-settings',
            'uri'    => 'miniapp-manager/wechat/config',
        ],
        [
            'parent' => '微信小程序',
            'title'  => '用户管理',
            'icon'   => 'feather icon-users',
            'uri'    => 'miniapp-manager/wechat/users',
        ],
        [
            'parent' => '微信小程序',
            'title'  => '小程序码',
            'icon'   => 'feather icon-image',
            'uri'    => 'miniapp-manager/wechat/qrcodes',
        ],
        // 支付宝小程序（预留）
        [
            'parent' => '小程序管理',
            'title'  => '支付宝小程序',
            'uri'    => '',
            'icon'   => 'feather icon-credit-card',
        ],
        [
            'parent' => '支付宝小程序',
            'title'  => '平台配置',
            'icon'   => 'feather icon-settings',
            'uri'    => 'miniapp-manager/alipay/config',
        ],
        // 抖音小程序（预留）
        [
            'parent' => '小程序管理',
            'title'  => '抖音小程序',
            'uri'    => '',
            'icon'   => 'feather icon-video',
        ],
        [
            'parent' => '抖音小程序',
            'title'  => '平台配置',
            'icon'   => 'feather icon-settings',
            'uri'    => 'miniapp-manager/douyin/config',
        ],
    ];

	public function register()
	{
		// 注册路由中间件别名
		$this->app['router']->aliasMiddleware('miniapp.config', CheckPlatformConfig::class);

		// API 路由需在 register() 中加载，因为 boot()/init() 会跳过 API 请求
		$this->loadApiRoutes();
	}

	public function init()
	{
		parent::init();
	}

	public function settingForm()
	{
		return new Setting($this);
	}

	/**
	 * 加载小程序 API 路由
	 */
	protected function loadApiRoutes(): void
	{
		// if ($this->app->routesAreCached()) {
		// 	return;
		// }
		$apiRouteFile = $this->path('src/Http/Api/routes.php');
        if(file_exists($apiRouteFile)){
            \Illuminate\Support\Facades\Route::prefix('member-api')
                ->middleware('api')
                ->namespace('Ycookies\MiniappManager\Http\Api\Controllers')
                ->group($apiRouteFile);
        }
		// if (file_exists($apiRouteFile)) {
		// 	Route::prefix('member-api')
		// 		->middleware('api')
		// 		->group($apiRouteFile);
		// }
	}
}
