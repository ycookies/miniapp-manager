<?php
namespace Ycookies\MiniappManager\Services;

use App\Models\Hotel\Setting;
use EasyWeChat\Factory;
use App\Models\Hotel\WxopenMiniProgramOauth;
use App\Models\Hotel\WxopenMiniProgramVersion;

// 微信开放平台接口服务
class WxopenService {
    public $openPlatform;
    public $oauthInfo;

    public function __construct() {
        $cache_wxopen = \Cache::get('wxopen_config');
        if(empty($cache_wxopen)){
            $wxopen_config = Setting::getlists([], 'wxopen');
            \Cache::put('wxopen_config',$wxopen_config);
        }else{
            $wxopen_config = $cache_wxopen;
        }
        $open_config   = [
            'app_id'  => $wxopen_config['wxopen_app_id'],
            'secret'  => $wxopen_config['wxopen_secret'],
            'token'   => !empty($wxopen_config['wxopen_Token']) ? $wxopen_config['wxopen_Token'] : '',
            'aes_key' => !empty($wxopen_config['wxopen_aesKey']) ? $wxopen_config['wxopen_aesKey'] : ''
        ];
        $openPlatform  = \EasyWeChat\Factory::openPlatform($open_config);
        $this->openPlatform = $openPlatform;
        //return $openPlatform;
    }

    public  function inits(){
        return $this->openPlatform;
    }
    // 获取授权账号信息
    public function getAuthorizer($app_id,$hotel_id = '',$app_type = 'minapp'){
        if(!empty($hotel_id)){
            $oauth        = WxopenMiniProgramOauth::where(['hotel_id' => $hotel_id,'app_type'=>$app_type])->first();
            $app_id = $oauth->AuthorizerAppid;
        }
        // 获取授权账号信息
        $res = $this->openPlatform->getAuthorizer($app_id);
        return $res;
    }
    // 通过子酒店ID 获取小程序操作对象
    public function hotelMiniProgram($hotel_id){
        $oauth        = WxopenMiniProgramOauth::where(['hotel_id' => $hotel_id,'app_type'=>'minapp'])->first();
        if(!$oauth){
            return false;
        }
        $miniProgram  = $this->openPlatform->miniProgram($oauth->AuthorizerAppid, $oauth->authorizer_refresh_token);
        return $miniProgram;
    }

    // 通过子商户号  获取小程序操作对象
    public function submchidMiniProgram($sub_mch_id){
        $oauth        = WxopenMiniProgramOauth::where(['sub_mch_id' => $sub_mch_id,'app_type'=>'minapp'])->first();
        if(empty($oauth->AuthorizerAppid)){
            return false;
        }
        $miniProgram  = $this->openPlatform->miniProgram($oauth->AuthorizerAppid, $oauth->authorizer_refresh_token);
        return $miniProgram;
    }

    // 通过app_id 获取小程序操作对象
    public function miniProgram($app_id){
        $oauth        = WxopenMiniProgramOauth::where(['AuthorizerAppid' => $app_id])->first();
        $miniProgram  = $this->openPlatform->miniProgram($oauth->AuthorizerAppid, $oauth->authorizer_refresh_token);
        return $miniProgram;
    }

    // 通过app_id 获取公众号操作对象
    public function wxgzh($app_id){
        $oauth        = WxopenMiniProgramOauth::where(['AuthorizerAppid' => $app_id])->first();
        $officialAccount  = $this->openPlatform->officialAccount($oauth->AuthorizerAppid, $oauth->authorizer_refresh_token);
        return $officialAccount;
    }
    // 通过子酒店ID 获取公众号操作对象
    public function hotelWxgzh($hotel_id){
        $oauth        = WxopenMiniProgramOauth::where(['hotel_id' => $hotel_id,'app_type'=>'wxgzh'])->first();
        $officialAccount  = $this->openPlatform->officialAccount($oauth->AuthorizerAppid, $oauth->authorizer_refresh_token);
        return $officialAccount;
    }

    // 获取小程序的授权信息
    public function getOauthInfo($app_id,$hotel_id = '',$sub_mch_id = '',$app_type = 'minapp'){
        if(!empty($app_id)){
            $oauth        = WxopenMiniProgramOauth::where(['AuthorizerAppid' => $app_id])->first();
        }
        if(!empty($hotel_id)){
            $oauth        = WxopenMiniProgramOauth::where(['hotel_id' => $hotel_id,'app_type'=>$app_type])->first();
        }
        if(!empty($sub_mch_id)){
            $oauth        = WxopenMiniProgramOauth::where(['sub_mch_id' => $sub_mch_id,'app_type'=>$app_type])->first();
        }
        return $oauth;
    }

    // 支付
    public function payment($app_id){
        $pay = $this->openPlatform;
    }

    // 根据路径生成小程序码
    public function getMinappQrcode($sub_mch_id,$hotel_id,$path,$filenamefull ='',$is_force = 1){
        $full_path = public_path('uploads/images').'/'.$filenamefull;
        if(empty($is_force)){
            if(file_exists($full_path)){
                $qrcode_url = env('APP_URL').'/uploads/images/'.$filenamefull;
                return $qrcode_url;
            }
        }
        if(!empty($sub_mch_id)){
            $miniProgram = $this->submchidMiniProgram($sub_mch_id);
        }
        if(!empty($hotel_id)){
            $miniProgram = $this->hotelMiniProgram($hotel_id);
        }
        $response = $miniProgram->app_code->getQrCode($path);
        if(empty($filenamefull)){
            $filenamefull = 'minapp--'.time().'.png';
        }
        if ($response instanceof \EasyWeChat\Kernel\Http\StreamResponse) {

            if(file_exists($full_path)){
                unlink($full_path);
            }
            $filename = $response->saveAs(public_path('uploads/images'), $filenamefull);
            if(!file_exists($full_path)){
                return returnData(204, 0, [], '保存小程序二维码失败');
            }
            $qrcode_url = env('APP_URL').'/uploads/images/'.$filenamefull;
            return $qrcode_url;
        }
        return false;
    }

    // 根据路径生成 小程序码(数量暂无限制)
    public function getUnlimitedQRCode($sub_mch_id,$hotel_id,$scene,$optional,$filenamefull ='',$is_force = 1){
        $full_path = public_path('uploads/images').'/'.$filenamefull;
        if(empty($is_force)){
            if(file_exists($full_path)){
                $qrcode_url = env('APP_URL').'/uploads/images/'.$filenamefull;
                return $qrcode_url;
            }
        }
        if(!empty($sub_mch_id)){
            $miniProgram = $this->submchidMiniProgram($sub_mch_id);
        }
        if(!empty($hotel_id)){
            $miniProgram = $this->hotelMiniProgram($hotel_id);
        }
        if(empty($scene)){
            $scene = 'a=1';
        }
        $response = $miniProgram->app_code->getUnlimit($scene,$optional);
        if(empty($filenamefull)){
            $filenamefull = 'minapp--'.time().'.png';
        }
        if ($response instanceof \EasyWeChat\Kernel\Http\StreamResponse) {

            if(file_exists($full_path)){
                unlink($full_path);
            }
            $filename = $response->saveAs(public_path('uploads/images'), $filenamefull);
            if(!file_exists($full_path)){
                return returnData(204, 0, [], '保存小程序二维码失败');
            }
            $qrcode_url = env('APP_URL').'/uploads/images/'.$filenamefull;
            return $qrcode_url;
        }
        return false;
    }


    // 提交酒店小程序隐私保护配置信息
    public function tijiaoYinsi($hotel_id){
        $data = [
            ["privacy_key"  => "UserInfo","privacy_text" => "用于酒店会员展示"],// 用户信息（微信昵称、头像）
            ["privacy_key" => "Location", "privacy_text" => "用于酒店导航"], // 定位
            ["privacy_key" => "Address", "privacy_text" => "用于寄送物品"], // 地址
            ["privacy_key" => "Invoice", "privacy_text" => "用于酒店订房开票"], //发票
            ["privacy_key" => "RunData", "privacy_text" => "用于推荐合适的运动补给"], // 微信运动数据
            ["privacy_key" => "Record", "privacy_text" => "用于住宿客服咨询"],
            ["privacy_key" => "Album", "privacy_text" => "用于酒店住客评价"],
            ["privacy_key" => "Camera", "privacy_text" => "用于酒店住客评价拍照"],
            ["privacy_key" => "PhoneNumber", "privacy_text" => "用于酒店预订信息填写"],
            //["privacy_key" => "Contact", "privacy_text" => "通讯录（仅写入）权限"],
            ["privacy_key" => "DeviceInfo", "privacy_text" => "用于优化程序"], //设备信息
            ["privacy_key" => "EXIDNumber", "privacy_text" => "用于酒店预订信息填写"], // 身份证号码
            ["privacy_key" => "EXOrderInfo", "privacy_text" => "用于酒店预订订单展示"], // 订单信息
            //["privacy_key" => "EXUserPublishContent", "privacy_text" => "发布内容"],
            //["privacy_key" => "EXUserFollowAcct", "privacy_text" => "所关注账号"],
            //["privacy_key" => "EXUserOpLog", "privacy_text" => "操作日志"],
            ["privacy_key" => "AlbumWriteOnly", "privacy_text" => "用于保存酒店推荐海报"],
            ["privacy_key" => "LicensePlate", "privacy_text" => "用于酒店住宿登记车牌号"],
            ["privacy_key" => "BlueTooth", "privacy_text" => "用于酒店住宿连接Wifi"],
            ["privacy_key" => "CalendarWriteOnly", "privacy_text" => "用于酒店住中服务特约叫醒"],
            ["privacy_key" => "Email", "privacy_text" => "用于发送酒店住宿发票"],
            ["privacy_key" => "MessageFile", "privacy_text" => "用于提交会员认证资料"],
            ["privacy_key" => "ChooseLocation", "privacy_text" => "用于酒店周边信息推荐"],
            //["privacy_key" => "Accelerometer", "privacy_text" => "加速传感器"],
            //["privacy_key" => "Compass", "privacy_text" => "磁场传感器"],
            //["privacy_key" => "DeviceMotion", "privacy_text" => "方向传感器"],
            //["privacy_key" => "Gyroscope", "privacy_text" => "陀螺仪传感器"],
            ["privacy_key" => "Clipboard", "privacy_text" => "用于邮寄地址复制"]
        ];
        $mp['setting_list']  = $data;
        $mp['owner_setting'] = [
            'contact_phone'  => '13725589225',
            'contact_weixin' => 'gemo9966',
            'notice_method'  => '通过弹窗', // 通知方式
        ];

        $miniProgram = $this->hotelMiniProgram($hotel_id);
        $res         = $miniProgram->setting->setPrivacysetting($mp);
        addlogs('wxminapp_setPrivacysetting',[$hotel_id,$mp],$res);
        return $res;
    }



}