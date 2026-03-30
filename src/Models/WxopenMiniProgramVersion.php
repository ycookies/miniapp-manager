<?php

namespace Ycookies\MiniappManager\Models;
use Illuminate\Database\Eloquent\Model;

class WxopenMiniProgramVersion extends Model {
    const Audit_status_arr = [
        '1' => '未提交审核',
        '2' => '审核中',
        '3' => '审核未通过',
        '4' => '审核通过',
        '5' => '已发布',
    ];
    const Audit_status_label = [
        '1' => 'gray',
        '2' => 'info',
        '3' => 'danger',
        '4' => 'primary',
        '5' => 'success'
    ];
    protected $table = 'yx_wxopen_mini_program_version';
    protected $guarded = [];

}
