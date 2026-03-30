<?php
namespace Ycookies\MiniappManager\Models;

use Illuminate\Database\Eloquent\Model;
class MiniprogramPage extends Model
{
    const Type_arr = [
        '' => '全部',
        '1' => '基础页面',
        '2' => '营销页面',
        '3' => '订单页面',
        '4' => '插件页面',
        '5' => '触发功能',
    ];
    const Types_arr = [
        '1' => '基础页面',
        '2' => '营销页面',
        '3' => '订单页面',
        '4' => '插件页面',
        '5' => '触发功能',
    ];
	
    protected $table = 'yx_miniprogram_pages';
    protected $guarded = [];

    
}
