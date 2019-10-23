<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Class CreatePlexUsersTable
 */
class CreatePlexUsersTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() : void {
        Schema::create('plex_users', static function (Blueprint $table) {
            $table->unsignedBigInteger('id');
            $table->string('uuid')->nullable();
            $table->string('title')->nullable();
            $table->string('username')->nullable();
            $table->string('email')->nullable();
            $table->boolean('admin')->default(false);
            $table->boolean('guest')->default(false);
            $table->boolean('friend')->default(false);
            $table->longText('avatar')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() : void {
        Schema::dropIfExists('plex_users');
    }

}
