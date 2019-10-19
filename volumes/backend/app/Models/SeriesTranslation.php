<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class SeriesTranslation
 * @package App\Models
 */
class SeriesTranslation extends Model {

    /**
     * @inheritDoc
     * @var string
     */
    protected $table = 'series_translations';

    /**
     * @inheritDoc
     * @var array
     */
    protected $fillable = [
        'id',
        'locale_ar_title',
        'locale_ar_overview',
        'locale_bg_title',
        'locale_bg_overview',
        'locale_bs_title',
        'locale_bs_overview',
        'locale_ca_title',
        'locale_ca_overview',
        'locale_cs_title',
        'locale_cs_overview',
        'locale_da_title',
        'locale_da_overview',
        'locale_de_title',
        'locale_de_overview',
        'locale_el_title',
        'locale_el_overview',
        'locale_en_title',
        'locale_en_overview',
        'locale_es_title',
        'locale_es_overview',
        'locale_et_title',
        'locale_et_overview',
        'locale_fa_title',
        'locale_fa_overview',
        'locale_fi_title',
        'locale_fi_overview',
        'locale_fr_title',
        'locale_fr_overview',
        'locale_he_title',
        'locale_he_overview',
        'locale_hr_title',
        'locale_hr_overview',
        'locale_hu_title',
        'locale_hu_overview',
        'locale_id_title',
        'locale_id_overview',
        'locale_it_title',
        'locale_it_overview',
        'locale_ja_title',
        'locale_ja_overview',
        'locale_ko_title',
        'locale_ko_overview',
        'locale_lt_title',
        'locale_lt_overview',
        'locale_nl_title',
        'locale_nl_overview',
        'locale_no_title',
        'locale_no_overview',
        'locale_pl_title',
        'locale_pl_overview',
        'locale_pt_title',
        'locale_pt_overview',
        'locale_ro_title',
        'locale_ro_overview',
        'locale_ru_title',
        'locale_ru_overview',
        'locale_sk_title',
        'locale_sk_overview',
        'locale_sr_title',
        'locale_sr_overview',
        'locale_sv_title',
        'locale_sv_overview',
        'locale_th_title',
        'locale_th_overview',
        'locale_tr_title',
        'locale_tr_overview',
        'locale_uk_title',
        'locale_uk_overview',
        'locale_vi_title',
        'locale_vi_overview',
        'locale_zh_title',
        'locale_zh_overview',
    ];

    /**
     * @inheritDoc
     * @var array
     */
    protected $casts = [
        'id'                        =>  'integer',
        'locale_ar_title'			=>	'string',
        'locale_ar_overview'		=>	'string',
        'locale_bg_title'			=>	'string',
        'locale_bg_overview'		=>	'string',
        'locale_bs_title'			=>	'string',
        'locale_bs_overview'		=>	'string',
        'locale_ca_title'			=>	'string',
        'locale_ca_overview'		=>	'string',
        'locale_cs_title'			=>	'string',
        'locale_cs_overview'		=>	'string',
        'locale_da_title'			=>	'string',
        'locale_da_overview'		=>	'string',
        'locale_de_title'			=>	'string',
        'locale_de_overview'		=>	'string',
        'locale_el_title'			=>	'string',
        'locale_el_overview'		=>	'string',
        'locale_en_title'			=>	'string',
        'locale_en_overview'		=>	'string',
        'locale_es_title'			=>	'string',
        'locale_es_overview'		=>	'string',
        'locale_et_title'			=>	'string',
        'locale_et_overview'		=>	'string',
        'locale_fa_title'			=>	'string',
        'locale_fa_overview'		=>	'string',
        'locale_fi_title'			=>	'string',
        'locale_fi_overview'		=>	'string',
        'locale_fr_title'			=>	'string',
        'locale_fr_overview'		=>	'string',
        'locale_he_title'			=>	'string',
        'locale_he_overview'		=>	'string',
        'locale_hr_title'			=>	'string',
        'locale_hr_overview'		=>	'string',
        'locale_hu_title'			=>	'string',
        'locale_hu_overview'		=>	'string',
        'locale_id_title'			=>	'string',
        'locale_id_overview'		=>	'string',
        'locale_it_title'			=>	'string',
        'locale_it_overview'		=>	'string',
        'locale_ja_title'			=>	'string',
        'locale_ja_overview'		=>	'string',
        'locale_ko_title'			=>	'string',
        'locale_ko_overview'		=>	'string',
        'locale_lt_title'			=>	'string',
        'locale_lt_overview'		=>	'string',
        'locale_nl_title'			=>	'string',
        'locale_nl_overview'		=>	'string',
        'locale_no_title'			=>	'string',
        'locale_no_overview'		=>	'string',
        'locale_pl_title'			=>	'string',
        'locale_pl_overview'		=>	'string',
        'locale_pt_title'			=>	'string',
        'locale_pt_overview'		=>	'string',
        'locale_ro_title'			=>	'string',
        'locale_ro_overview'		=>	'string',
        'locale_ru_title'			=>	'string',
        'locale_ru_overview'		=>	'string',
        'locale_sk_title'			=>	'string',
        'locale_sk_overview'		=>	'string',
        'locale_sr_title'			=>	'string',
        'locale_sr_overview'		=>	'string',
        'locale_sv_title'			=>	'string',
        'locale_sv_overview'		=>	'string',
        'locale_th_title'			=>	'string',
        'locale_th_overview'		=>	'string',
        'locale_tr_title'			=>	'string',
        'locale_tr_overview'		=>	'string',
        'locale_uk_title'			=>	'string',
        'locale_uk_overview'		=>	'string',
        'locale_vi_title'			=>	'string',
        'locale_vi_overview'		=>	'string',
        'locale_zh_title'			=>	'string',
        'locale_zh_overview'		=>	'string',
    ];

    /**
     * @inheritDoc
     * @var array
     */
    protected $dates = [
        'created_at',
        'updated_at'
    ];

    /**
     * Get series information from translation
     * @return BelongsTo
     */
    public function series() : BelongsTo {
        return $this->belongsTo(Series::class, 'id', 'id');
    }

}
