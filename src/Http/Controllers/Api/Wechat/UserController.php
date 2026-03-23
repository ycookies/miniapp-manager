<?php

namespace Ycookies\MiniappManager\Http\Controllers\Api\Wechat;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

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
            'nickname' => 'sometimes|string|max:50',
            'avatar'   => 'sometimes|string|max:500',
            'gender'   => 'sometimes|integer|in:0,1,2',
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
