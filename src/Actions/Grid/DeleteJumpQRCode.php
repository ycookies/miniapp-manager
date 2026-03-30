<?php

namespace Ycookies\MiniappManager\Actions\Grid;

use Dcat\Admin\Grid\RowAction;
use Dcat\Admin\Actions\Response;
use Illuminate\Http\Request;
use Dcat\Admin\Admin;

// 扫普通二维码打开小程序  删除已设置的二维码规则
class DeleteJumpQRCode extends RowAction
{
    /**
     * 标题
     *
     * @return string
     */
    public function title()
    {
        return '删除';
    }

    /**
     * Handle the action request.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function handle(Request $request)
    {
        // 获取当前行ID
        $prefix = $this->getKey();
        $openPlatform = app('wechat.open');
        $miniProgram = $openPlatform->hotelMiniProgram(Admin::user()->hotel_id);
        $res = $miniProgram->setting->deleteJumpQRCode($prefix);

        addlogs('deleteJumpQRCode',['prefix'=> $prefix],$res);
        if(isset($res['errcode']) && $res['errcode'] == 0){
            return $this->response()->success('删除成功')->refresh();
        }
        return $this->response()->error('删除失败');
    }

    /**
     * @return string|void
     */
    public function confirm()
    {
        return [
            // 确认弹窗 title
            "您确定要删除规则吗？",
            // 确认弹窗 content
            $this->row->prefix,
        ];
    }


    /**
     * @return array
     */
    protected function parameters()
    {
        return [];
    }
}
