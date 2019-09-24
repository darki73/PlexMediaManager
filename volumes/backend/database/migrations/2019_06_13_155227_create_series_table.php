<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Class CreateSeriesTable
 */
class CreateSeriesTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() : void {
        Schema::create('series', static function (Blueprint $table) {
            $table->unsignedInteger('id')->primary();
            // Series Title Columns
            $table->string('title');
            $table->string('original_title');
            $table->string('local_title');

            // Series Languages Columns
            $table->string('original_language')->nullable();
            $table->json('languages')->nullable();

            // Series General Columns
            $table->longText('overview')->nullable();
            $table->json('genres')->nullable();
            $table->string('homepage')->nullable();
            $table->unsignedInteger('runtime')->nullable();
            $table->unsignedInteger('status');
            $table->unsignedInteger('episodes_count')->nullable();
            $table->unsignedInteger('seasons_count')->nullable();
            $table->string('release_date')->nullable();
            $table->string('last_air_date')->nullable();
            $table->string('origin_country')->nullable();
            $table->boolean('in_production')->default(false);
            $table->json('production_companies')->nullable();
            $table->json('creators')->nullable();
            $table->json('networks')->nullable();

            // Series Popularity Fields
            $table->double('vote_average')->nullable();
            $table->unsignedInteger('vote_count')->nullable();
            $table->double('popularity')->nullable();

            // Series Images Fields
            $table->string('backdrop')->nullable();
            $table->string('poster')->nullable();

            // Series Timestamps Fields
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() : void {
        Schema::dropIfExists('series');
    }

}
