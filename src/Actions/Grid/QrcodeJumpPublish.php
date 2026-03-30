<?php
namespace Ycookies\MiniappManager\Actions\Grid;

use Dcat\Admin\Grid\RowAction;
use Dcat\Admin\Actions\Response;
use Illuminate\Http\Request;
use Dcat\Admin\Admin;

// 扫普通二维码打开小程序  发布已设置的二维码规则
class QrcodeJumpPublish extends RowAction
{
    /**
     * 标题
     *
     * @return string
     */
    public function title()
    {
        return '发布 &nbsp;';
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
        $prefix = $this->getKey();
        $openPlatform = app('wechat.open');
        $miniProgram = $openPlatform->hotelMiniProgram(Admin::user()->hotel_id);
        $res = $miniProgram->setting->publishJumpQRCode($prefix);
        addlogs('publishJumpQRCode',['prefix'=> $prefix],$res);

        if(isset($res['errcode']) && $res['errcode'] == 0){
            return $this->response()->success('发布成功')->refresh();
        }
        return $this->response()->error('发布失败');

    }

    /**
     * @return string|void
     */
    public function confirm()
    {
        return [
            // 确认弹窗 title
            "您确定要现在全网发布吗？",
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
