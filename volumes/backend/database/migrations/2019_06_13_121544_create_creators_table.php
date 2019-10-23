<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Class CreateCreatorsTable
 */
class CreateCreatorsTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() : void {
        Schema::create('creators', static function (Blueprint $table) {
            $table->unsignedInteger('id')->primary();
            $table->string('name');
            $table->unsignedInteger('gender')->nullable();
            $table->string('photo')->nullable();
            $table->string('birthday')->nullable();
            $table->string('deathday')->nullable();
            $table->longText('biography')->nullable();
            $table->string('birth_place')->nullable();
            $table->double('popularity')->nullable();
            $table->string('imdb_id')->nullable();
            $table->string('homepage')->nullable();
            $table->boolean('adult')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() : void {
        Schema::dropIfExists('creators');
    }

}
