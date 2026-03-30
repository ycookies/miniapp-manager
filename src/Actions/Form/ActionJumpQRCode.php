<?php

namespace Ycookies\MiniappManager\Actions\Form;

use App\Models\Hotel\WxopenMiniProgramOauth;
use App\Models\Hotel\WxopenMiniProgramVersion;
use Dcat\Admin\Admin;
use Dcat\Admin\Contracts\LazyRenderable;
use Dcat\Admin\Http\JsonResponse;
use Dcat\Admin\Traits\LazyWidget;
use Dcat\Admin\Widgets\Form;
use Dcat\Admin\Widgets\Alert;
use Ycookies\MiniappManager\Models\MiniprogramPage;
class ActionJumpQRCode extends Form implements LazyRenderable {
    use LazyWidget;

    protected $payload = [];
    protected $authinfo;

    public function handle(array $input) {
        $page_path = $input['page_path'] ?? '';
        $scene = $input['scene'] ?? '';
        if(empty($page_path)){
            return JsonResponse::make()->error('页面路径不能为空');
        }
        $miniapp = app('wechat.miniapp');
        $res = $miniapp->getUnlimitedQrcode($scene, $page_path);
        if(isset($res['errcode']) && $res['errcode'] == 0){
            return $this->response()->success('操作成功')->refresh();
        }
        $errormsg = '操作失败';
        if(isset($res['errcode']) && $res['errcode'] == 85069){
            $errormsg = '验证 校验文件失败';
        }
        return JsonResponse::make()->error($errormsg);
    }

    public function default() {

        $data = [];
        return $data;
    }

    public function form() {
        $this->html('');
        $this->select('page_path','小程序页面')->options($this->getPageOptions())->required();
        $this->text('scene','场景值')->help('例如：user_id=1');

        $this->disableResetButton();
    }

    protected function getPageOptions() {
        // 获取小程序页面选项
        $pages = MiniprogramPage::where('hotel_id', 143)->get();
        $options = [];
        foreach ($pages as $page) {
            
            $options[$page->path] = $page->name.'('.$page->path.')';
        }
        return $options;
    }

}
