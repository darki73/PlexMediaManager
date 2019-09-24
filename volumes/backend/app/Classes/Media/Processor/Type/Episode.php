<?php namespace App\Classes\Media\Processor\Type;

use App\Models\GuestStar;
use App\Models\CrewMember;
use App\Models\Episode as EpisodeModel;

/**
 * Class Episode
 * @package App\Classes\Media\Processor\Type
 */
class Episode extends AbstractType {

    /**
     * @inheritDoc
     * @var string
     */
    protected $model = EpisodeModel::class;

    /**
     * @inheritDoc
     * @var array
     */
    protected $separateEntities = [
        'crew'          =>  CrewMember::class,
        'guestStars'    =>  GuestStar::class
    ];

    protected function executeTypeSpecificMethods(): AbstractType {
        return $this;
    }

}
