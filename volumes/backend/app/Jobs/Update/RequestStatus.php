<?php namespace App\Jobs\Update;

use App\Models\Series;
use App\Models\Episode;
use App\Models\Request;
use App\Jobs\AbstractLongQueueJob;
use App\Classes\Media\Source\Source;
use App\Events\Requests\RequestCompleted;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class RequestStatus
 * @package App\Jobs\Update
 */
class RequestStatus extends AbstractLongQueueJob {

    protected $requests = null;

    /**
     * Plex Movies Collection
     * @var array
     */
    protected array $plexMovies = [];

    /**
     * SeriesIndexers constructor.
     */
    public function __construct() {
        $this->setAttempts(25);
        $this->setTags('update-status', 'requests');
        $this->requests = Request::all();
        $this->plexMovies = Source::movies()->list();
    }

    /**
     * @inheritDoc
     */
    public function handle(): void {
        $completed = [];
        foreach ($this->requests as $request) {
            $localTitle = sprintf('%s (%d)', $request->title, $request->year);
            if ($request->status === 1) {
                switch ($request->request_type) {
                    case 0: // Series
                        $series = Series::where('local_title', '=', $localTitle)->first();
                        if ($series !== null) {
                            $missingEpisodesCount = Episode::missing()->where('series_id', '=', $series->id)->count();
                            $downloadedEpisodesCount = Episode::downloaded()->where('series_id', '=', $series->id)->count();
                            if ($downloadedEpisodesCount > $missingEpisodesCount) {
                                $completed[] = [
                                    'request_id'    =>  $request->id,
                                    'item'          =>  [
                                        'id'        =>  $series->id,
                                        'title'     =>  $series->title,
                                        'year'      =>  $request->year
                                    ],
                                    'notify'        =>  $request->user
                                ];
                            }
                        }
                        break;
                    case 1: // Movies
                        foreach ($this->plexMovies as $movie) {
                            if (false !== stripos($movie['original_name'], $localTitle)) {
                                $completed[] = [
                                    'request_id'    =>  $request->id,
                                    'item'          =>  [
                                        'id'        =>  -1,
                                        'title'     =>  $request->title,
                                        'year'      =>  $request->year
                                    ],
                                    'notify'        =>  $request->user
                                ];
                            }
                        }
                        break;
                }
            }
        }

        foreach ($completed as $status) {
            $request = Request::find($status['request_id']);
            if ($request !== null) {
                $request->update([
                    'status'    =>  3
                ]);
                event(new RequestCompleted($status['item'], $status['notify']));
            }
        }

    }


}
