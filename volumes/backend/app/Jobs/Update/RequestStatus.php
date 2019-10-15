<?php namespace App\Jobs\Update;

use App\Events\Requests\RequestCompleted;
use App\Models\Series;
use App\Models\Episode;
use App\Models\Request;
use App\Jobs\AbstractLongQueueJob;

/**
 * Class RequestStatus
 * @package App\Jobs\Update
 */
class RequestStatus extends AbstractLongQueueJob {

    protected $requests = null;

    /**
     * SeriesIndexers constructor.
     */
    public function __construct() {
        $this->setAttempts(25);
        $this->setTags('update-status', 'requests');
        $this->requests = Request::all();
    }

    /**
     * @inheritDoc
     */
    public function handle(): void {
        $completed = [];
        foreach ($this->requests as $request) {
            if ($request->request_type === 0 && $request->status === 1) { // Series only, for now
                $series = Series::where('local_title', '=', sprintf('%s (%d)', $request->title, $request->year))->first();
                if ($series !== null) {
                    $missingEpisodesCount = Episode::missing()->where('series_id', '=', $series->id)->count();
                    $downloadedEpisodesCount = Episode::downloaded()->where('series_id', '=', $series->id)->count();
                    if ($downloadedEpisodesCount > $missingEpisodesCount) {
                        $completed[] = [
                            'request_id'    =>  $request->id,
                            'series'        =>  [
                                'title'     =>  $series->title,
                                'year'      =>  $request->year
                            ],
                            'notify'        =>  $request->user
                        ];
                    }
                }
            }
        }

        foreach ($completed as $status) {
            $request = Request::find($status['request_id']);
            if ($request !== null) {
                $request->update([
                    'status'    =>  3
                ]);
                event(new RequestCompleted($status['series'], $status['notify']));
            }
        }

    }


}
