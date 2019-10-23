<?php namespace App\Models;

use Illuminate\Support\Arr;
use Laravel\Scout\Searchable;


/**
 * Class Movie
 * @package App\Models
 */
class Movie extends AbstractMediaModel {
    use Searchable;

    /**
     * @inheritDoc
     * @var string|null
     */
    protected ?string $translationClass = MovieTranslation::class;

    /**
     * @inheritDoc
     * @var string
     */
    protected $table = 'movies';

    /**
     * @inheritDoc
     * @var array
     */
    protected $fillable = [
        'id',
        'title',
        'original_title',
        'local_title',
        'original_language',
        'languages',
        'overview',
        'tagline',
        'genres',
        'homepage',
        'runtime',
        'status',
        'adult',
        'imdb_id',
        'release_date',
        'production_companies',
        'production_countries',
        'vote_average',
        'vote_count',
        'popularity',
        'budget',
        'revenue',
        'backdrop',
        'poster',
    ];

    /**
     * @inheritDoc
     * @var array
     */
    protected $casts = [
        'id'                    =>  'integer',
        'title'                 =>  'string',
        'original_title'        =>  'string',
        'local_title'           =>  'string',
        'original_language'     =>  'string',
        'languages'             =>  'array',
        'overview'              =>  'string',
        'tagline'               =>  'string',
        'genres'                =>  'array',
        'homepage'              =>  'string',
        'runtime'               =>  'integer',
        'status'                =>  'integer',
        'adult'                 =>  'boolean',
        'imdb_id'               =>  'string',
        'release_date'          =>  'string',
        'production_companies'  =>  'array',
        'production_countries'  =>  'array',
        'vote_average'          =>  'double',
        'vote_count'            =>  'integer',
        'popularity'            =>  'double',
        'budget'                =>  'integer',
        'revenue'               =>  'integer',
        'backdrop'              =>  'string',
        'poster'                =>  'string',
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
     * @inheritDoc
     * @return string
     */
    public function searchableAs() : string {
        return 'movies';
    }

    /**
     * @inheritDoc
     * @return array
     */
    public function toSearchableArray() : array {
        $array = Arr::only($this->toArray(), [
            'id', 'title', 'original_title', 'original_language', 'overview',
            'tagline', 'genres', 'adult', 'production_companies', 'production_countries',
            'release_date', 'vote_average', 'popularity'
        ]);
        if ($array['id'] === 0) {
            return [];
        }
        $translations = $this->getModelTranslations(true);
        $array['title'] = $translations['title'];
        $array['overview'] = $translations['overview'];
        $array['production_companies'] = ProductionCompany::findMany($array['production_companies'])->toArray();
        $array['production_countries'] = ProductionCountry::findMany($array['production_countries'])->toArray();
        $array['genres'] = Genre::findMany($array['genres'])->toArray();
        return $array;
    }

}


