<?php

namespace Ycookies\MiniappManager;

use Dcat\Admin\Extend\Setting as Form;

class Setting extends Form
{
    public function form()
    {
        $this->text('app_id')->required()->help('微信小程序 AppID');
        $this->password('app_secret')->required()->help('微信小程序 AppSecret');
        $this->text('token')->help('消息推送 Token（可选）');
        $this->text('encoding_aes_key')->help('消息加解密密钥（可选）');
    }
}
