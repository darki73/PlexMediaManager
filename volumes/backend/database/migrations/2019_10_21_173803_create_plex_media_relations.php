<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePlexMediaRelations extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() : void {
        Schema::create('plex_media_relations', static function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('model');
            $table->unsignedInteger('media_id');
            $table->longText('plex_url');
            $table->string('server_id');
            $table->string('server_name');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() : void {
        Schema::dropIfExists('plex_media_relations');
    }

}
