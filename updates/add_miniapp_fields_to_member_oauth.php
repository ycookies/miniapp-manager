<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMiniappFieldsToMemberOauth extends Migration
{
    public function up(): void
    {
        Schema::table('member_oauth', function (Blueprint $table) {
            $table->string('union_id', 255)->default('')->comment('UnionId')->after('open_id');
            $table->string('session_key', 255)->default('')->comment('会话密钥(加密存储)')->after('union_id');
        });
    }

    public function down(): void
    {
        Schema::table('member_oauth', function (Blueprint $table) {
            $table->dropColumn(['union_id', 'session_key']);
        });
    }
}
