<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('iracing_customer_id')->nullable()->unique()->after('role');
            $table->text('access_token')->nullable()->after('iracing_customer_id');
            $table->text('refresh_token')->nullable()->after('access_token');
            $table->timestamp('token_expires_at')->nullable()->after('refresh_token');
            $table->boolean('iracing_linked')->default(false)->after('token_expires_at');
        });

        DB::table('users')->where('role', 'pilot')->update(['role' => 'driver']);

        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('driver')->change();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique('users_iracing_customer_id_unique');
            $table->dropColumn([
                'iracing_customer_id',
                'access_token',
                'refresh_token',
                'token_expires_at',
                'iracing_linked',
            ]);
        });
    }
};
