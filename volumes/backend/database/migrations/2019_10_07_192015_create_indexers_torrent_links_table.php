<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIndexersTorrentLinksTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() : void {
        Schema::create('indexers_torrent_links', function (Blueprint $table) {
            $table->unsignedBigInteger('series_id');
            $table->unsignedInteger('season');
            $table->longText('torrent_file');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() : void {
        Schema::dropIfExists('indexers_torrent_links');
    }
}
