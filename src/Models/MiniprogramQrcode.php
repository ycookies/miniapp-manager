<?php

namespace Ycookies\MiniappManager\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;
use Illuminate\Database\Eloquent\Model;

class MiniprogramQrcode extends Model
{
    use HasDateTimeFormatter;

    protected $table = 'yx_miniprogram_qrcode';

    protected $guarded = [];
}
