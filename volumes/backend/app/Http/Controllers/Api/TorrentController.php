<?php namespace App\Http\Controllers\Api;

use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use App\Classes\DownloadManager;
use App\Classes\Torrent\Torrent;
use Illuminate\Http\JsonResponse;
use App\Jobs\Sync\Episodes as SyncEpisodesJob;

/**
 * Class TorrentController
 * @package App\Http\Controllers\Api
 */
class TorrentController extends APIController {

    /**
     * Get torrent client information
     * @param Request $request
     * @return JsonResponse
     */
    public function clientInformation(Request $request) : JsonResponse {
        $torrent = new Torrent;
        $response = $this->extractTorrentClientInformation($torrent);

        return $this->sendResponse(
            'Successfully fetched information about Torrent Client',
            $response
        );
    }

    /**
     * Synchronize downloaded items with the database
     * @param Request $request
     * @return JsonResponse
     */
    public function synchronizeItems(Request $request) : JsonResponse {
        $manager = new DownloadManager;
        $manager->series()->cleanEmptyDirectories();
        $manager->movies()->cleanEmptyDirectories();
        dispatch(new SyncEpisodesJob);
        return $this->sendResponse('Successfully created synchronization request');
    }

    /**
     * Get list of completed torrents with commands to move the files
     * @param Request $request
     * @return void
     */
    public function listCompletedTorrents(Request $request) : void {
        $manager = new DownloadManager;
        $files = [];

        $manager->series();
        foreach ($manager->listDownloadedFiles() as $file) {
            $files[] = $file;
        }

        $manager->movies();
        foreach ($manager->listDownloadedFiles() as $file) {
            $files[] = $file;
        }

        foreach ($files as $file) {
            if (isset($file['fix_audio']) && $file['fix_audio']) {
                fix_lostfilm_audio_tracks($file['fix_path']);
            }
        }

        foreach ($files as $file) {
            echo sprintf('mv "%s" "%s"', $file['downloads_path'], $file['local_path']) . PHP_EOL;
        }

        die();
    }

    /**
     * Extract torrent client information from the base class
     * @param Torrent $torrent
     * @return array
     */
    private function extractTorrentClientInformation(Torrent $torrent) : array {
        $classParts = explode('\\', $torrent->getClientClass());
        $clientName = Arr::last($classParts);

        return [
            'provider'      =>  $clientName,
            'version'       =>  $torrent->client()->getImplementationVersion()
        ];
    }

}
