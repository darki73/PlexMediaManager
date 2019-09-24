<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Class CreateProductionCompaniesTable
 */
class CreateProductionCompaniesTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() : void {
        Schema::create('production_companies', static function (Blueprint $table) {
            $table->unsignedInteger('id')->primary();
            $table->string('name');
            $table->string('country')->nullable();
            $table->string('logo')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() : void {
        Schema::dropIfExists('production_companies');
    }

}
