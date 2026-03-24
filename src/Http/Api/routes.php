<?php

use Ycookies\MiniappManager\Http\Api\Controllers\Wechat;
use Ycookies\MiniappManager\Http\Api\Controllers\Douyin;
use Ycookies\MiniappManager\Http\Api\Controllers\Alipay;
use Illuminate\Support\Facades\Route;

// === 微信小程序 API ===
Route::prefix('miniapp/wechat')->group(function () {

    // 无需登录
    Route::post('login', [Wechat\AuthController::class, 'login']);

    // 需要登录 (JWT memberapi)
    Route::middleware('member.apiAuth')->group(function () {
        Route::post('phone', [Wechat\AuthController::class, 'phone']);
        Route::get('user', [Wechat\UserController::class, 'show']);
        Route::put('user', [Wechat\UserController::class, 'update']);
    });
});

// === 支付宝小程序 API（预留） ===
// Route::prefix('api/miniapp/alipay')->group(function () { });

// === 抖音小程序 API（预留） ===
// Route::prefix('api/miniapp/douyin')->group(function () { });
