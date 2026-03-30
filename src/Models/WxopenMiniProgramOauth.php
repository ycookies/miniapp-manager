<?php

namespace Ycookies\MiniappManager\Models;
use Illuminate\Database\Eloquent\Model;

class WxopenMiniProgramOauth extends Model {

    const InfoType_arr =[
        'authorized' => '已授权',
        'unauthorized' => '授权取消',
    ];
    const App_type_arr = [
        'minapp' => '微信小程序',
        'wxgzh' => '微信公众号',
    ];

    protected $table = 'yx_wxopen_mini_program_oauth';
    protected $guarded = [];
    // 新增授权
    public static function addOauth($message) {
        $AuthorizerAppid = !empty($message['AuthorizerAppid']) ? $message['AuthorizerAppid'] : '';

        $insdata = [
            'user_id'                      => !empty($message['user_id']) ? $message['user_id'] : '',
            'hotel_id'                     => !empty($message['hotel_id']) ? $message['hotel_id'] : '',
            'app_name'                     => '',
            'CreateTime'                   => !empty($message['CreateTime']) ? $message['CreateTime'] : '',
            'InfoType'                     => !empty($message['InfoType']) ? $message['InfoType'] : '',
            'AuthorizerAppid'              => $AuthorizerAppid,
            'AuthorizationCode'            => !empty($message['AuthorizationCode']) ? str_replace('queryauthcode@@@','',$message['AuthorizationCode']) : '',
            'AuthorizationCodeExpiredTime' => !empty($message['AuthorizationCodeExpiredTime']) ? $message['AuthorizationCodeExpiredTime'] : '',
            'PreAuthCode'                  => !empty($message['PreAuthCode']) ? str_replace('preauthcode@@@','',$message['PreAuthCode']) : '',
            'authorizer_access_token'      => !empty($message['authorizer_access_token']) ? $message['authorizer_access_token'] : '',
            'expires_in'                   => !empty($message['expires_in']) ? $message['expires_in'] : '',
            'authorizer_refresh_token'     => !empty($message['authorizer_refresh_token']) ? str_replace('refreshtoken@@@','',$message['authorizer_refresh_token']) : '',
        ];
        $res = app('wechat.open')->getAuthorizer($AuthorizerAppid);
        if(!empty($res['authorizer_info'])){
            $authorizer_info = $res['authorizer_info'];

            $insdata['app_name'] = $authorizer_info['nick_name'];
            $insdata['ToUserName'] = $authorizer_info['user_name'];
            $insdata['qrcode_url'] = $authorizer_info['qrcode_url'];
            $insdata['principal_name'] = $authorizer_info['principal_name'];
            $insdata['app_name'] = $authorizer_info['nick_name'];
        }
        $info = self::where(['AuthorizerAppid'=>$AuthorizerAppid])->first();
        if($info){
            //self::where(['AuthorizerAppid'=>$AuthorizerAppid])->update($insdata);
        }else{
            self::create($insdata);
        }
        return true;
    }

    // 更新授权
    public static function upOauth($message) {
        self::addOauth($message);
    }

    // 取消授权
    public  static function unOauth($message) {
        $AuthorizerAppid = !empty($message['AuthorizerAppid']) ? $message['AuthorizerAppid'] : '';
        $info = self::where(['AuthorizerAppid'=>$AuthorizerAppid])->first();
        if($info){
            $updata = [
                'InfoType' => !empty($message['InfoType']) ? $message['InfoType']:'',
            ];
            self::where(['AuthorizerAppid'=>$AuthorizerAppid])->update($updata);
        }
        return true;
    }
}
