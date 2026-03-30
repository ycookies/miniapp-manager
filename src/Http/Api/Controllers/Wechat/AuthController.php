<?php

namespace Ycookies\MiniappManager\Http\Api\Controllers\Wechat;

use App\Models\MemberOauth;
use App\Models\MemberUser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Ycookies\MiniappManager\Services\WechatMiniappService;
use Dedoc\Scramble\Attributes\QueryParameter;
use Dedoc\Scramble\Attributes\BodyParameter;
use Dedoc\Scramble\Attributes\Group;

#[Group('小程序管理插件-微信小程序','微信小程序',1)]
class AuthController extends Controller
{
    /**
     * 微信小程序登录
     * 
     * 接收 wx.login() 返回的 code，换取 openid 并签发 JWT
     * @unauthenticated
     */
    public function login(Request $request): JsonResponse
    {

        $request->validate([
            // 微信登录 code
            'code' => ['required', 'string'],
        ],[
            'code.required' => '请提供登录 code',
        ]);
        $service = new WechatMiniappService();

        //try {
            $session = $service->codeToSession($request->input('code'));
        // } catch (\Throwable $e) {
        //     return response()->json(['code' => 500, 'msg' => '微信登录失败'], 500);
        // }

        if (empty($session['openid'])) {
            return response()->json(['code' => 500, 'msg' => '获取 openid 失败'], 500);
        }

        $openid     = $session['openid'];
        $unionid    = $session['unionid'] ?? '';
        $sessionKey = $session['session_key'] ?? '';

        $oauth = MemberOauth::where('type', MemberOauth::WX_MINI)
            ->where('open_id', $openid)
            ->first();

        $memberUser = DB::transaction(function () use ($oauth, $openid, $unionid, $sessionKey) {
            if ($oauth) {
                $oauth->update([
                    'union_id'    => $unionid ?: $oauth->union_id,
                    'session_key' => $sessionKey ? Crypt::encryptString($sessionKey) : $oauth->session_key,
                ]);
                return MemberUser::find($oauth->member_user_id);
            }

            $memberUser = MemberUser::create([
                'username' => 'wx_' . Str::random(8),
                'password' => bcrypt(Str::random(16)),
                'status'   => 1,
            ]);

            MemberOauth::create([
                'member_user_id' => $memberUser->id,
                'type'           => MemberOauth::WX_MINI,
                'open_id'        => $openid,
                'union_id'       => $unionid,
                'session_key'    => $sessionKey ? Crypt::encryptString($sessionKey) : '',
            ]);

            return $memberUser;
        });

        if (!$memberUser) {
            return response()->json(['code' => 500, 'msg' => '用户创建失败'], 500);
        }

        $token = auth('memberapi')->fromUser($memberUser);

        return response()->json([
            'code' => 0,
            'msg'  => 'ok',
            'data' => [
                'access_token' => $token,
                'token_type'   => 'bearer',
                'expires_in'   => auth('memberapi')->factory()->getTTL() * 60,
                'user'         => [
                    'id'       => $memberUser->id,
                    'nickname' => $memberUser->nickname,
                    'avatar'   => $memberUser->avatar,
                    'phone'    => $memberUser->phone,
                ],
            ],
        ]);
    }

     /**
     * 更新用户头像，昵称等信息
     * 
     */
    public function updateProfile(Request $request)
    {
        $request->validate([
            /**
             * 昵称 
             */
            'nickname' => ['required', 'string', 'max:255'],
            /**
             * 头像上传
             */
            'avatar'   => ['nullable', 'string','url'],
        ]);

        $user = auth('memberapi')->user();
        if (!$user) {
            return response()->json(['code' => 401, 'msg' => '未登录'], 401);
        }

        $data = $request->only(['nickname', 'avatar']);
        // 只更新有值的字段
        $update = array_filter($data, function ($v) {
            return !is_null($v) && $v !== '';
        });
        if (empty($update)) {
            return response()->json(['code' => 422, 'msg' => '无可更新内容'], 422);
        }

        $user->update($update);

        return response()->json([
            'code' => 0,
            'msg'  => 'ok',
            'data' => [
                'id'       => $user->id,
                'nickname' => $user->nickname,
                'avatar'   => $user->avatar,
                'phone'    => $user->phone,
            ],
        ]);
    }

    /**
     * 获取手机号
     * 
     */
    public function phone(Request $request): JsonResponse
    {
        $request->validate([
            'code' => 'required|string',
        ]);

        $user = auth('memberapi')->user();
        if (!$user) {
            return response()->json(['code' => 401, 'msg' => '未登录'], 401);
        }

        $service = new WechatMiniappService();

        try {
            $result = $service->getPhoneNumber($request->input('code'));
        } catch (\Throwable $e) {
            return response()->json(['code' => 500, 'msg' => '获取手机号失败'], 500);
        }

        if (empty($result['phone_info']['phoneNumber'])) {
            return response()->json(['code' => 500, 'msg' => '手机号解析失败'], 500);
        }

        $phone = $result['phone_info']['phoneNumber'];
        $user->update(['phone' => $phone]);

        return response()->json([
            'code' => 0,
            'msg'  => 'ok',
            'data' => ['phone' => $phone],
        ]);
    }
}
