<?php namespace App\Console\Commands\People;

use Carbon\Carbon;
use App\Models\Creator;
use App\Models\GuestStar;
use App\Models\CrewMember;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class PeopleUpdate
 * @package App\Console\Commands\People
 */
class PeopleUpdate extends Command {

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'people:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update information for all people';

    /**
     * Creators collection
     * @var Creator[]|Collection|null
     */
    protected ?Collection $creatorsCollection = null;

    /**
     * Crew Members collection
     * @var Collection|null
     */
    protected ?Collection $crewMembersCollection = null;

    /**
     * Guest Stars collection
     * @var Collection|null
     */
    protected ?Collection $guestStarsCollection = null;

    /**
     * Indicates whether application is ready to handle commands
     * @var bool
     */
    protected bool $ready = true;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct() {
        parent::__construct();
        try {
            $this->creatorsCollection = Creator::all();
            $this->crewMembersCollection = CrewMember::all();
            $this->guestStarsCollection = GuestStar::all();
        } catch (\Exception $exception) {
            $this->ready = false;
        }
    }

    /**
     * Execute the console command.
     * @return void
     */
    public function handle() : void {
        if (! $this->ready) {
            return;
        }

        $updatableRecords = $this->updatableRecords();
        $countRecords = \count($updatableRecords);

        $progressBar = $this->output->createProgressBar($countRecords);
        if ($countRecords > 0) {
            $progressBar->setFormat(' %current%/%max% [%bar%] %percent:3s%% %elapsed:6s%/%estimated:-6s% %memory:6s%');
        }
        foreach ($updatableRecords as $id => $references) {
            $database = new \App\Classes\TheMovieDB\TheMovieDB;
            $creatorData = $database->people()->findByID($id);
            $processor = (new \App\Classes\TheMovieDB\Processor\Person($creatorData))->toArray(['id']);
            foreach ($references as $reference) {
                $reference->update($processor);
            }
            $progressBar->advance();
        }
        $progressBar->finish();
    }

    /**
     * Check whether we can or should update person
     * @param Model $person
     * @return bool
     */
    protected function canOrShouldUpdate(Model $person) : bool {
        /**
         * @var Carbon $updatedAt
         */
//        $updatedAt = $person->updated_at;
//        $nextUpdate = $updatedAt->addMonth();
//        return $person->popularity === null || $nextUpdate->diffInMonths($updatedAt) > 0;
        return $person->popularity === null;
    }

    /**
     * Get list of creator which we can or should update
     * @return array
     */
    protected function updatableRecords() : array {
        $people = [];
        foreach ($this->creatorsCollection as $creator) {
            if ($this->canOrShouldUpdate($creator)) {
                $people[$creator->id][] = $creator;
            }
        }

        foreach ($this->crewMembersCollection as $crewMember) {
            if ($this->canOrShouldUpdate($crewMember)) {
                $people[$crewMember->id][] = $crewMember;
            }
        }

        foreach ($this->guestStarsCollection as $guestStar) {
            if ($this->canOrShouldUpdate($guestStar)) {
                $people[$guestStar->id][] = $guestStar;
            }
        }

        return $people;
    }

}
