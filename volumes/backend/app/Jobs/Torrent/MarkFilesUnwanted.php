<?php namespace App\Jobs\Torrent;

use App\Models\Series;
use App\Classes\Torrent\Torrent;
use App\Jobs\AbstractLongQueueJob;

/**
 * Class MarkFilesUnwanted
 * @package App\Jobs\Torrent
 */
class MarkFilesUnwanted extends AbstractLongQueueJob {

    /**
     * Series Instance
     * @var Series|null
     */
    private $series = null;

    /**
     * Seasons Array
     * @var array
     */
    private $seasons = [];

    /**
     * MarkFilesUnwanted constructor.
     * @param Series $series
     * @param array $seasons
     */
    public function __construct(Series $series, array $seasons) {
        $this->setAttempts(1);
        $this->series = $series;
        $this->seasons = $seasons;
    }

    /**
     * @inheritDoc
     */
    public function handle() : void {
//        $torrentClient = new Torrent;
//        $removeFromDownloading = [];
//        foreach ($torrentClient->listTorrents() as $item) {
//            $torrentHash = $item['hash'];
//            $torrentFiles = $torrentClient->torrentFiles($torrentHash);
//            foreach ($torrentFiles as $index => $file) {
//                $fileName = $file['name'];
//                if (
//                    false !== stripos($fileName, $this->series->title)
//                    || false !== stripos(str_replace('.', ' ', $fileName), $this->series->title)
//                ) {
//                    preg_match('/s(\d{1,2})e(\d{1,3})/i', $fileName, $matches);
//                    if (\count($matches) < 3) {
//                        app('log')->info('There were less than 3 matches, but we expected exactly 3');
//                        $removeFromDownloading[$torrentHash][] = $index;
//                    } else {
//                        $season = (integer) $matches[1];
//                        $episode = (integer) $matches[2];
//                        $data = isset($seasons[$season]) ? $this->seasons[$season] : null;
//                        if ($data === null) {
//                            $removeFromDownloading[$torrentHash][] = $index;
//                        } else {
//                            if (!in_array($episode, $data['episodes'])) {
//                                $removeFromDownloading[$torrentHash][] = $index;
//                            }
//                        }
//                    }
//                }
//            }
//        }
//
//
//        foreach ($removeFromDownloading as $hash => $files) {
//            foreach ($files as $file) {
//                $torrentClient->doNotDownload($hash, $file);
//                usleep(1000000);
//            }
//        }
    }

}
