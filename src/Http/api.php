<?php

use Ycookies\MiniappManager\Http\Controllers\Api;
use Illuminate\Support\Facades\Route;

// === 微信小程序 API ===
Route::prefix('api/miniapp/wechat')->group(function () {
    // 无需登录
    Route::post('login', [Api\Wechat\AuthController::class, 'login']);

    // 需要登录 (JWT memberapi)
    Route::middleware('member.apiAuth')->group(function () {
        Route::post('phone', [Api\Wechat\AuthController::class, 'phone']);
        Route::get('user', [Api\Wechat\UserController::class, 'show']);
        Route::put('user', [Api\Wechat\UserController::class, 'update']);
    });
});

// === 支付宝小程序 API（预留） ===
// Route::prefix('api/miniapp/alipay')->group(function () { });

// === 抖音小程序 API（预留） ===
// Route::prefix('api/miniapp/douyin')->group(function () { });
