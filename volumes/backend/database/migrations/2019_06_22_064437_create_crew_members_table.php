<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Class CreateCrewMembersTable
 */
class CreateCrewMembersTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() : void {
        Schema::create('crew_members', static function (Blueprint $table) {
            $table->unsignedInteger('id')->primary();
            $table->string('name');
            $table->unsignedInteger('gender')->nullable();
            $table->string('photo')->nullable();
            $table->string('birthday')->nullable();
            $table->string('deathday')->nullable();
            $table->string('biography')->nullable();
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
        Schema::dropIfExists('crew_members');
    }

}
