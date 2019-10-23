<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Class CreateSeriesTranslationsTable
 */
class CreateSeriesTranslationsTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() : void {
        Schema::create('series_translations', static function (Blueprint $table) {
            $table->unsignedInteger('id')->primary();
            $table->string('locale_ar_title')->nullable();
            $table->longText('locale_ar_overview')->nullable();
            $table->string('locale_de_title')->nullable();
            $table->longText('locale_de_overview')->nullable();
            $table->string('locale_en_title')->nullable();
            $table->longText('locale_en_overview')->nullable();
            $table->string('locale_es_title')->nullable();
            $table->longText('locale_es_overview')->nullable();
            $table->string('locale_fr_title')->nullable();
            $table->longText('locale_fr_overview')->nullable();
            $table->string('locale_ja_title')->nullable();
            $table->longText('locale_ja_overview')->nullable();
            $table->string('locale_ko_title')->nullable();
            $table->longText('locale_ko_overview')->nullable();
            $table->string('locale_no_title')->nullable();
            $table->longText('locale_no_overview')->nullable();
            $table->string('locale_ru_title')->nullable();
            $table->longText('locale_ru_overview')->nullable();
            $table->string('locale_uk_title')->nullable();
            $table->longText('locale_uk_overview')->nullable();
            $table->string('locale_zh_title')->nullable();
            $table->longText('locale_zh_overview')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     * @return void
     */
    public function down() : void {
        Schema::dropIfExists('series_translations');
    }

}

