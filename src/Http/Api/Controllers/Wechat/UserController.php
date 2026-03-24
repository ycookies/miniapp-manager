<?php
namespace Ycookies\MiniappManager\Http\Api\Controllers\Wechat;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Dedoc\Scramble\Attributes\QueryParameter;
use Dedoc\Scramble\Attributes\BodyParameter;
use Dedoc\Scramble\Attributes\Group;

#[Group('小程序管理插件-微信小程序','微信小程序',1)]
class UserController extends Controller
{
    /**
     * 获取当前用户信息
     */
    public function show(): JsonResponse
    {
        $user = auth('memberapi')->user();
        if (!$user) {
            return response()->json(['code' => 401, 'msg' => '未登录'], 401);
        }

        return response()->json([
            'code' => 0,
            'msg'  => 'ok',
            'data' => [
                'id'       => $user->id,
                'username' => $user->username,
                'nickname' => $user->nickname,
                'avatar'   => $user->avatar,
                'phone'    => $user->phone,
                'email'    => $user->email,
                'gender'   => $user->gender,
                'points'   => $user->points,
                'balance'  => $user->balance,
                'status'   => $user->status,
            ],
        ]);
    }

    /**
     * 更新用户信息（昵称、头像等）
     */
    public function update(Request $request): JsonResponse
    {
        $user = auth('memberapi')->user();
        if (!$user) {
            return response()->json(['code' => 401, 'msg' => '未登录'], 401);
        }

        $data = $request->validate([
            // 昵称、头像、性别等可选字段
            'nickname' => ['sometimes', 'string', 'max:50'],
            // 头像URL
            'avatar'   => ['sometimes', 'string', 'max:500'],
            // 性别：0未知 1男 2女
            'gender'   => ['sometimes', 'integer', 'in:0,1,2'],
        ],[
            'nickname.string' => '昵称必须是字符串',
            'nickname.max'    => '昵称不能超过50个字符',
            'avatar.string'   => '头像必须是字符串',
            'avatar.max'      => '头像URL不能超过500个字符',
            'gender.integer'  => '性别必须是整数',
            'gender.in'       => '性别值不合法',
        ]);

        $user->update($data);

        return response()->json([
            'code' => 0,
            'msg'  => 'ok',
            'data' => [
                'id'       => $user->id,
                'nickname' => $user->nickname,
                'avatar'   => $user->avatar,
                'gender'   => $user->gender,
            ],
        ]);
    }
}
