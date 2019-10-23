<?php namespace App\Jobs\Download;

use App\Jobs\AbstractLongQueueJob;
use Illuminate\Support\Facades\File;
use App\Models\Series as SeriesModel;
use App\Classes\TheMovieDB\TheMovieDB;
use Intervention\Image\ImageManager;

/**
 * Class SeriesImages
 * @package App\Jobs\Download
 */
class SeriesImages extends AbstractLongQueueJob {

    /**
     * SeriesImages constructor.
     */
    public function __construct() {
        $this->setAttempts(25);
        $this->setTags('images', 'series');
    }

    /**
     * @inheritDoc
     * @return void
     */
    public function handle() : void {
        $seriesCollection = SeriesModel::where('local_title', '!=', null)->get();
        $database = new TheMovieDB;
        $configuration = $database->configuration();
        $images = [];

        foreach ($seriesCollection as $series) {
            $seriesBackdropFiles = $series->backdrop !== null ? $configuration->getRemoteImagePath($series->backdrop, 'backdrop') : [];
            $seriesPosterFiles = $series->poster !== null ? $configuration->getRemoteImagePath($series->poster, 'poster') : [];

            $baseStoragePath = storage_path(implode(DIRECTORY_SEPARATOR, [
                'app',
                'public',
                'images',
                'series',
                $series->id
            ]));

            foreach ($seriesBackdropFiles as $quality => $path) {
                $localPath = sprintf('%s/global/%s/%s', $baseStoragePath, $quality, $series->backdrop);
                if (!File::exists($localPath)) {
                    $images[$localPath] = $path;
                }
            }

            foreach ($seriesPosterFiles as $quality => $path) {
                $localPath = sprintf('%s/global/%s/%s', $baseStoragePath, $quality, $series->poster);
                if (! File::exists($localPath)) {
                    $images[$localPath] = $path;
                }
            }

            foreach ($series->seasons as $season) {
                $seasonPosterFiles = $season->poster !== null ? $configuration->getRemoteImagePath($season->poster, 'poster') : [];
                foreach ($seasonPosterFiles as $quality => $path) {
                    $localPath = sprintf('%s/seasons/%s/%s', $baseStoragePath, $quality, $season->poster);
                    if (! File::exists($localPath)) {
                        $images[$localPath] = $path;
                    }
                }
            }

        }

        $manager = new ImageManager([
            'driver'    =>  'imagick'
        ]);

        foreach ($images as $local => $remote) {
            $directory = pathinfo($local)['dirname'];
            if (! File::exists($directory)) {
                File::makeDirectory($directory, 0755, true);
            }
            $manager->make($remote)->save($local);
        }

    }

}
