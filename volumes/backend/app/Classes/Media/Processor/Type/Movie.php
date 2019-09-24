<?php namespace App\Classes\Media\Processor\Type;

use App\Models\Genre;
use App\Models\ProductionCompany;
use App\Models\ProductionCountry;
use App\Models\Movie as MovieModel;

/**
 * Class Movie
 * @package App\Classes\Media\Processor\Type
 */
class Movie extends AbstractType {

    /**
     * @inheritDoc
     * @var string
     */
    protected $model = MovieModel::class;

    /**
     * @inheritDoc
     * @var array
     */
    protected $separateEntities = [
        'productionCountries'   =>  ProductionCountry::class,
        'productionCompanies'   =>  ProductionCompany::class,
        'genres'                =>  Genre::class
    ];

    /**
     * @inheritDoc
     * @return AbstractType|static|self|$this
     */
    protected function executeTypeSpecificMethods(): AbstractType {
        $this
            ->extractProductionCompanies()
            ->extractProductionCountries();
        return $this;
    }

    /**
     * Extract production companies
     * @return Movie|static|self|$this
     */
    private function extractProductionCompanies() : self {
        $this->mergeSeparateEntities('productionCompanies', 'production_companies');
        return $this;
    }

    /**
     * Extract production countries
     * @return Movie|static|self|$this
     */
    private function extractProductionCountries() : self {
        $this->mergeSeparateEntities('productionCountries', 'production_countries');
        return $this;
    }

}
