<?php
namespace Ycookies\MiniappManager\Http\Renderable;

use Dcat\Admin\Contracts\LazyRenderable;
use Dcat\Admin\Traits\LazyWidget;
use Dcat\Admin\Widgets\Form;

class MinappPageQrcode extends Form implements LazyRenderable
{
    use LazyWidget;

    public function handle(array $input)
    {
        return $this->success('保存成功');
    }

    public function default()
    {
        $full_filename = str_replace('/','-',$this->payload['path']).'-qrcode.png';


        return [
            // 展示上个页面传递过来的值
            'hotel_id' => $this->payload['hotel_id'] ?? '',
            'path' => $this->payload['path'] ?? '',
            'name' => $this->payload['name'] ?? '',
        ];
    }

    public function form()
    {
        $full_filename = str_replace('/','-',$this->payload['hotel_id'].'-'.$this->payload['path']).'-qrcode.png';
        info($full_filename);
        $minapp_qrcode = app('wechat.open')->getMinappQrcode('',$this->payload['hotel_id'],'/'.$this->payload['path'],$full_filename,1);
        $qrcode_img = '';
        if($minapp_qrcode !== false){
            $qrcode_img = $minapp_qrcode;
        }

        $this->text('name','页面名称')->readOnly();
        $this->text('path','路径')->readOnly();
        $this->html('<div style="margin-top: 0px;"><img width="140" src="'.$qrcode_img.'" /></div>')->label('页面二维码');
        $this->disableSubmitButton();
        $this->disableResetButton();
    }
}
