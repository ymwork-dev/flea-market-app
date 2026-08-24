<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('postcode')->nullable()->after('email'); // 郵便番号
            $table->string('address')->nullable()->after('postcode'); // 住所
            $table->string('building')->nullable()->after('address'); // 建物名
            $table->string('img_url')->nullable()->after('building');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['postcode', 'address', 'building', 'img_url']);
        });
    }
};
