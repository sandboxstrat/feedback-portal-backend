<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFeedbackTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('feedback', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('user_id');
            $table->string('user_name');
            $table->string('game_id');
            $table->string('ip_address')->nullable();
            $table->string('ip_country')->nullable();
            $table->string('ip_region')->nullable();
            $table->string('ip_city')->nullable();
            $table->string('link')->nullable();
            $table->string('upload_image')->nullable();
            $table->string('feedback_option');
            $table->text('feedback');
            $table->boolean('viewed')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('feedback');
    }
}
