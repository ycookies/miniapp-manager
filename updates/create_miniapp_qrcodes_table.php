<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMiniappQrcodesTable extends Migration
{
    public function up(): void
    {
        Schema::create('miniapp_qrcodes', function (Blueprint $table) {
            $table->id();
            $table->string('scene', 255)->comment('场景值');
            $table->string('page', 255)->default('')->comment('页面路径');
            $table->unsignedSmallInteger('width')->default(430)->comment('宽度');
            $table->string('file_path', 500)->default('')->comment('存储路径');
            $table->string('remark', 255)->default('')->comment('备注');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('miniapp_qrcodes');
    }
}
