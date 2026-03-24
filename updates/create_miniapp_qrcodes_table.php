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
            $table->string('page', 255)->comment('页面路径');
            $table->string('scene', 255)->nullable()->comment('场景值');
            $table->unsignedSmallInteger('width')->nullable()->default(430)->comment('宽度');
            $table->string('file_path', 500)->nullable()->default('')->comment('存储路径');
            $table->string('remark', 255)->nullable()->default('')->comment('备注');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('miniapp_qrcodes');
    }
}
