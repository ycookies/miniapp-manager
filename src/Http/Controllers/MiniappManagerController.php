<?php

namespace Ycookies\MiniappManager\Http\Controllers;

use Dcat\Admin\Layout\Content;
use Dcat\Admin\Admin;
use Illuminate\Routing\Controller;
use Ycookies\MiniappManager\MiniappManagerServiceProvider;

class MiniappManagerController extends Controller
{
    public function index(Content $content)
    {
        $appId = MiniappManagerServiceProvider::setting('app_id');

        return $content
            ->title('微信小程序')
            ->description($appId ? 'AppID: ' . $appId : '请先在扩展设置中配置 AppID')
            ->body(Admin::view('Ycookies.MiniappManager::index'));
    }
}