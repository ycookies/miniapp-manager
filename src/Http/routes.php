<?php

use Ycookies\MiniappManager\Http\Controllers;
use Illuminate\Support\Facades\Route;

// === 配置页（无需验证即可访问） ===
Route::get('miniapp-manager/{platform}/config', [Controllers\WxMinappController::class, 'index'])
    ->where('platform', 'wechat|alipay|douyin');
Route::post('miniapp-manager/{platform}/config/save', [Controllers\WxMinappController::class, 'save'])
    ->where('platform', 'wechat|alipay|douyin');
Route::post('miniapp-manager/{platform}/config/verify', [Controllers\WxMinappController::class, 'verify'])
    ->where('platform', 'wechat|alipay|douyin');

// === 微信小程序（需配置验证通过） ===
Route::middleware('miniapp.config:wechat')->group(function () {
    Route::resource('miniapp-manager/wechat/users', Controllers\Wechat\UserController::class)->only(['index', 'show']);
    Route::resource('miniapp-manager/wechat/qrcodes', Controllers\Wechat\QrcodeController::class);
});