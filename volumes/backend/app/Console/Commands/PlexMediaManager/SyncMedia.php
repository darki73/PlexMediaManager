<?php namespace App\Console\Commands\PlexMediaManager;


use App\Classes\Plex\Plex;
use App\Models\Movie;
use App\Models\PlexMediaRelation;
use App\Models\Series;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;

/**
 * Class SyncMedia
 * @package App\Console\Commands\PlexMediaManager
 */
class SyncMedia extends Command {

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pmm:sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync media between Plex and local database';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * Execute the console command.
     * @return void
     */
    public function handle() : void {
        $this->info('Retrieving information from the Plex API...');
        $data = (new Plex)->internal()->contents(true);

        foreach ($data as $server => $contents) {
            foreach ($contents as $item) {
                $title = $item['title'];
                $releaseDate = $item['released'];
                $releaseYear = Arr::first(explode('-', $releaseDate));
                $originCountry = null;
                if (false !== strpos($title, '(')) {
                    preg_match('/\(\d{4}\)/', $title, $matches);
                    if (\count($matches) > 0) {
                        $match = $matches[0];
                        $title = trim(str_replace($match, '', $title));
                    }
                    preg_match('/\(\S{2}\)/', $title, $matches);
                    if (\count($matches) > 0) {
                        $originCountry = str_replace(['(', ')'], '', $matches[0]);
                        $title = trim(str_replace($matches[0], '', $title));
                    }
                }

                $seriesQuery = Series::query()->where('title', '=', $title)->where('release_date', 'LIKE', $releaseYear . '-%');
                if ($originCountry !== null) {
                    $seriesQuery = $seriesQuery->where('origin_country', '=', $originCountry);
                }
                $series = $seriesQuery->first();

                $movie = Movie::where('title', '=', $title)->where('release_date', 'LIKE', $releaseYear . '-%')->first();
                if ($series !== null && $movie === null) {
                    $data = [
                        'model'         =>  Series::class,
                        'media_id'      =>  $series->id,
                        'plex_url'      =>  $item['watch'],
                        'server_id'     =>  $server,
                        'server_name'   =>  $item['server_name']
                    ];
                    $model = PlexMediaRelation::where('model', '=', Series::class)->where('media_id', '=', $data['media_id'])->where('server_id', '=', $server)->first();
                    if ($model !== null) {
                        $model->update($data);
                    } else {
                        PlexMediaRelation::create($data);
                    }
                } else if ($movie !== null && $series === null) {
                    $data = [
                        'model'         =>  Movie::class,
                        'media_id'      =>  $movie->id,
                        'plex_url'      =>  $item['watch'],
                        'server_id'     =>  $server,
                        'server_name'   =>  $item['server_name']
                    ];
                    $model = PlexMediaRelation::where('model', '=', Movie::class)->where('media_id', '=', $data['media_id'])->where('server_id', '=', $server)->first();
                    if ($model !== null) {
                        $model->update($data);
                    } else {
                        PlexMediaRelation::create($data);
                    }
                } else {
                    $this->info('There is a movie and a series for: ' . $title);
                }
            }
        }
    }

}
