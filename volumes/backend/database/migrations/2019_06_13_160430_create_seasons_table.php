<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Class CreateSeasonsTable
 */
class CreateSeasonsTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() : void {
        Schema::create('seasons', static function (Blueprint $table) {
            $table->unsignedInteger('id')->primary();
            $table->string('name');
            $table->unsignedInteger('series_id');
            $table->unsignedInteger('season_number');
            $table->longText('overview')->nullable();
            $table->unsignedInteger('episodes_count');
            $table->string('poster')->nullable();
            $table->string('air_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() : void {
        Schema::dropIfExists('seasons');
    }

}
