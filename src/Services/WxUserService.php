<?php
namespace Ycookies\MiniappManager\Services;

use App\Models\Hotel\Users as UsersModel;
use App\Models\Hotel\UsersInfo as UsersInfoModel;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * 微信用户服务类
 * @package App\Services
 * anthor Fox
 */
class WxUserService extends BaseService {

    // 增加用户
    public function addUser($data) {


        $insdata = [
            'mall_id'        => $data['mall_id'],
            'mch_id'         => $data['mch_id'],
            'username'       => $data['username'],
            'password'       => Hash::make($data['password']),
            'nickname'       => $data['nickname'],
            'remember_token' => '',
            'mobile'         => $data['mobile'],
            'openid'         => $data['openid'],
            'created_at'     => Carbon::now(),
        ];
        // 增加用户基本信息
        $st = UsersModel::where(['openid' => $data['openid']])->first();
        if(!$st){
            $api_token = Str::random(64);
            $insdata['api_token'] = $api_token;
            $info    = UsersModel::updateOrCreate(['openid' => $data['openid']], $insdata);
            $user_id = $info->id;

        }else{
            $api_token = $st->api_token;
            $user_id = $st->id;
        }

        // 增加用户的详细信息
        $insdata2 = [
            'user_id'          => $user_id,
            'avatar'           => $data['avatarUrl'],
            'gender'           => $data['gender'],
            'platform_user_id' => $data['openid'],
            'integral'         => 0,
            'total_integral'   => 0,
            'balance'          => 0,
            'total_balance'    => 0,
            'parent_id'        => 0,
            'is_blacklist'     => 0, // 是否黑名单
            'contact_way'      => $data['mobile'], // 联系方式
            'remark'           => '',
            'is_delete'        => 0,
            'junior_at'        => null,
            'platform'         => 'wxapp',
            'temp_parent_id'   => 0,
        ];

        // 增加上级
        $infoid = UsersInfoModel::updateOrCreate(['platform_user_id' => $data['openid']], $insdata2);
        return [
            'user_id' => $user_id,
            'api_token' => $api_token
        ];
    }
    //
    public function infos(){

    }
}