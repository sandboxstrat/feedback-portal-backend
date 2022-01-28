<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGamesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        
        Schema::create('games', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('url',100)->unique();
            $table->string('website')->nullable();
            $table->string('publisher')->nullable();
            $table->string('publisher_website')->nullable();
            $table->string('developer')->nullable();
            $table->string('developer_website')->nullable();
            $table->string('background_image')->nullable();
            $table->string('options_background_image')->nullable();
            $table->string('logo')->nullable();
            $table->text('description')->nullable();
            $table->boolean('feedback_page')->default(0);
            $table->boolean('active')->default(1);
            $table->string('created_by');
            $table->string('last_edited_by');
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
        Schema::dropIfExists('games');
    }
}
