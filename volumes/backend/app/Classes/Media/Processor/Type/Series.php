<?php namespace App\Classes\Media\Processor\Type;

use App\Models\Genre;
use App\Models\Season;
use App\Models\Creator;
use App\Models\Network;
use App\Models\ProductionCompany;
use App\Models\SeriesTranslation;
use App\Models\Series as SeriesModel;

/**
 * Class Series
 * @package App\Classes\Media\Processor\Type
 */
class Series extends AbstractType {

    /**
     * @inheritDoc
     * @var array
     */
    protected $separateEntities = [
        'creators'              =>  Creator::class,
        'genres'                =>  Genre::class,
        'networks'              =>  Network::class,
        'productionCompanies'   =>  ProductionCompany::class,
        'seasons'               =>  Season::class,
        'translations'          =>  SeriesTranslation::class
    ];

    /**
     * @inheritDoc
     * @var string
     */
    protected $model = SeriesModel::class;

    /**
     * @inheritDoc
     * @return AbstractType|static|self|$this
     */
    protected function executeTypeSpecificMethods(): AbstractType {
        $this
            ->extractProductionCompanies()
            ->extractCreators()
            ->extractNetworks();
        return $this;
    }

    /**
     * Extract series creators
     * @return Series|static|self|$this
     */
    protected function extractCreators() : self {
        $this->mergeSeparateEntities('creators', 'creators');
        return $this;
    }

    /**
     * Extract series networks
     * @return Series|static|self|$this
     */
    protected function extractNetworks() : self {
        $this->mergeSeparateEntities('networks', 'networks');
        return $this;
    }

    /**
     * Extract series production companies
     * @return Series|static|self|$this
     */
    protected function extractProductionCompanies() : self {
        $this->mergeSeparateEntities('productionCompanies', 'production_companies');
        return $this;
    }

}
