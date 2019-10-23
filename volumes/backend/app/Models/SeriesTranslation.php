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
        'locale_de_title',
        'locale_de_overview',
        'locale_en_title',
        'locale_en_overview',
        'locale_es_title',
        'locale_es_overview',
        'locale_fr_title',
        'locale_fr_overview',
        'locale_ja_title',
        'locale_ja_overview',
        'locale_ko_title',
        'locale_ko_overview',
        'locale_no_title',
        'locale_no_overview',
        'locale_ru_title',
        'locale_ru_overview',
        'locale_uk_title',
        'locale_uk_overview',
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
        'locale_de_title'			=>	'string',
        'locale_de_overview'		=>	'string',
        'locale_en_title'			=>	'string',
        'locale_en_overview'		=>	'string',
        'locale_es_title'			=>	'string',
        'locale_es_overview'		=>	'string',
        'locale_fr_title'			=>	'string',
        'locale_fr_overview'		=>	'string',
        'locale_ja_title'			=>	'string',
        'locale_ja_overview'		=>	'string',
        'locale_ko_title'			=>	'string',
        'locale_ko_overview'		=>	'string',
        'locale_no_title'			=>	'string',
        'locale_no_overview'		=>	'string',
        'locale_ru_title'			=>	'string',
        'locale_ru_overview'		=>	'string',
        'locale_uk_title'			=>	'string',
        'locale_uk_overview'		=>	'string',
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
