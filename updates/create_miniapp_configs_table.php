<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMiniappConfigsTable extends Migration
{
    public function up(): void
    {
        Schema::create('miniapp_configs', function (Blueprint $table) {
            $table->id();
            $table->string('platform', 30)->unique()->comment('平台标识: wechat/alipay/douyin');
            $table->string('name', 50)->default('')->comment('平台显示名');
            $table->string('app_id', 100)->default('')->comment('AppID');
            $table->text('app_secret')->comment('AppSecret');
            $table->string('token', 255)->nullable()->comment('消息推送Token');
            $table->string('encoding_aes_key', 255)->nullable()->comment('消息加解密密钥');
            $table->tinyInteger('is_verified')->nullable()->default(0)->comment('是否已验证: 0否 1是');
            $table->tinyInteger('is_enabled')->nullable()->default(0)->comment('是否启用: 0否 1是');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('miniapp_configs');
    }
}
