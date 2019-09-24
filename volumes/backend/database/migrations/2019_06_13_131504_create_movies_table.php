<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Class CreateMoviesTable
 */
class CreateMoviesTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() : void {
        Schema::create('movies', static function (Blueprint $table) {
            $table->unsignedInteger('id')->primary();
            // Movie Title Columns
            $table->string('title');
            $table->string('original_title');
            $table->string('local_title');

            // Movie Languages Columns
            $table->string('original_language')->nullable();
            $table->json('languages')->nullable();

            // Movie General Columns
            $table->longText('overview')->nullable();
            $table->string('tagline')->nullable();
            $table->json('genres')->nullable();
            $table->string('homepage')->nullable();
            $table->unsignedInteger('runtime')->nullable();
            $table->unsignedInteger('status');
            $table->boolean('adult')->default(false);
            $table->string('imdb_id')->nullable();
            $table->string('release_date')->nullable();
            $table->json('production_companies')->nullable();
            $table->json('production_countries')->nullable();

            // Movie Popularity Fields
            $table->double('vote_average')->nullable();
            $table->unsignedInteger('vote_count')->nullable();
            $table->double('popularity')->nullable();

            // Movie Finances Fields
            $table->unsignedInteger('budget')->nullable();
            $table->unsignedInteger('revenue')->nullable();

            // Movie Images Fields
            $table->string('backdrop')->nullable();
            $table->string('poster')->nullable();

            // Movie Timestamps Fields
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() : void {
        Schema::dropIfExists('movies');
    }

}
