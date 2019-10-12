<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSeriesIndexersExcludes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() : void {
        Schema::create('series_indexers_excludes', static function (Blueprint $table) {
            $table->unsignedBigInteger('series_id');
            $table->unsignedInteger('season_number');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() : void {
        Schema::dropIfExists('series_indexers_excludes');
    }
}
