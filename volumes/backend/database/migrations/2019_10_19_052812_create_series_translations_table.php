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
            $table->string('locale_bg_title')->nullable();
            $table->longText('locale_bg_overview')->nullable();
            $table->string('locale_bs_title')->nullable();
            $table->longText('locale_bs_overview')->nullable();
            $table->string('locale_ca_title')->nullable();
            $table->longText('locale_ca_overview')->nullable();
            $table->string('locale_cs_title')->nullable();
            $table->longText('locale_cs_overview')->nullable();
            $table->string('locale_da_title')->nullable();
            $table->longText('locale_da_overview')->nullable();
            $table->string('locale_de_title')->nullable();
            $table->longText('locale_de_overview')->nullable();
            $table->string('locale_el_title')->nullable();
            $table->longText('locale_el_overview')->nullable();
            $table->string('locale_en_title')->nullable();
            $table->longText('locale_en_overview')->nullable();
            $table->string('locale_es_title')->nullable();
            $table->longText('locale_es_overview')->nullable();
            $table->string('locale_et_title')->nullable();
            $table->longText('locale_et_overview')->nullable();
            $table->string('locale_fa_title')->nullable();
            $table->longText('locale_fa_overview')->nullable();
            $table->string('locale_fi_title')->nullable();
            $table->longText('locale_fi_overview')->nullable();
            $table->string('locale_fr_title')->nullable();
            $table->longText('locale_fr_overview')->nullable();
            $table->string('locale_he_title')->nullable();
            $table->longText('locale_he_overview')->nullable();
            $table->string('locale_hr_title')->nullable();
            $table->longText('locale_hr_overview')->nullable();
            $table->string('locale_hu_title')->nullable();
            $table->longText('locale_hu_overview')->nullable();
            $table->string('locale_id_title')->nullable();
            $table->longText('locale_id_overview')->nullable();
            $table->string('locale_it_title')->nullable();
            $table->longText('locale_it_overview')->nullable();
            $table->string('locale_ja_title')->nullable();
            $table->longText('locale_ja_overview')->nullable();
            $table->string('locale_ko_title')->nullable();
            $table->longText('locale_ko_overview')->nullable();
            $table->string('locale_lt_title')->nullable();
            $table->longText('locale_lt_overview')->nullable();
            $table->string('locale_nl_title')->nullable();
            $table->longText('locale_nl_overview')->nullable();
            $table->string('locale_no_title')->nullable();
            $table->longText('locale_no_overview')->nullable();
            $table->string('locale_pl_title')->nullable();
            $table->longText('locale_pl_overview')->nullable();
            $table->string('locale_pt_title')->nullable();
            $table->longText('locale_pt_overview')->nullable();
            $table->string('locale_ro_title')->nullable();
            $table->longText('locale_ro_overview')->nullable();
            $table->string('locale_ru_title')->nullable();
            $table->longText('locale_ru_overview')->nullable();
            $table->string('locale_sk_title')->nullable();
            $table->longText('locale_sk_overview')->nullable();
            $table->string('locale_sr_title')->nullable();
            $table->longText('locale_sr_overview')->nullable();
            $table->string('locale_sv_title')->nullable();
            $table->longText('locale_sv_overview')->nullable();
            $table->string('locale_th_title')->nullable();
            $table->longText('locale_th_overview')->nullable();
            $table->string('locale_tr_title')->nullable();
            $table->longText('locale_tr_overview')->nullable();
            $table->string('locale_uk_title')->nullable();
            $table->longText('locale_uk_overview')->nullable();
            $table->string('locale_vi_title')->nullable();
            $table->longText('locale_vi_overview')->nullable();
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
