<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRedirectLogsTable extends Migration
{
    public function up()
    {
        Schema::create('redirect_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('redirect_id')->constrained();
            $table->timestamp('accessed_at');
            $table->ipAddress('ip_address');
            $table->text('user_agent');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('redirect_logs');
    }
}
