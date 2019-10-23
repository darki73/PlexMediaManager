<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Class CreateEpisodesTable
 */
class CreateEpisodesTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() : void {
        Schema::create('episodes', static function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->unsignedBigInteger('series_id');
            $table->unsignedBigInteger('season_id');
            $table->integer('season_number');
            $table->integer('episode_number');
            $table->longText('title');
            $table->longText('overview')->nullable();
            $table->string('production_code')->nullable();
            $table->string('release_date')->nullable();
            $table->string('still')->nullable();
            $table->integer('vote_count')->default(0);
            $table->float('vote_average')->default(0.0);
            $table->boolean('downloaded')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() : void {
        Schema::dropIfExists('episodes');
    }

}
