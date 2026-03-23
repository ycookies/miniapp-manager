<?php

namespace Ycookies\MiniappManager\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;
use Illuminate\Database\Eloquent\Model;

class MiniappQrcode extends Model
{
    use HasDateTimeFormatter;

    protected $table = 'miniapp_qrcodes';

    protected $guarded = [];
}
