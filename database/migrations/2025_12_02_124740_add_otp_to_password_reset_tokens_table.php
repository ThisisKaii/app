<?php
// database/migrations/xxxx_add_otp_to_password_reset_tokens_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('password_reset_tokens', function (Blueprint $table) {
            $table->string('otp', 6)->nullable()->after('token');
            $table->timestamp('otp_expires_at')->nullable()->after('otp');
        });
    }

    public function down()
    {
        Schema::table('password_reset_tokens', function (Blueprint $table) {
            $table->dropColumn(['otp', 'otp_expires_at']);
        });
    }
};