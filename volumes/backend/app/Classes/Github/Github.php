<?php namespace App\Classes\Github;

use GuzzleHttp\Client;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Symfony\Component\Finder\SplFileInfo;

/**
 * Class Github
 * @package App\Classes\Github
 */
class Github {

    /**
     * Github API Url for the application
     * @var string
     */
    protected string $githubAPIUrl = 'https://api.github.com/repos/darki73/PlexMediaManager/commits';

    /**
     * Get path to the git folder
     * @var string|null
     */
    protected ?string $gitFolder = null;

    /**
     * Indicates if application was loaded with the `git clone` command
     * @var bool
     */
    protected bool $isLoadedFromGithub = false;

    /**
     * Latest local commit
     * @var string|null
     */
    protected ?string $latestLocalCommit = null;

    /**
     * Latest remote commit
     * @var string|null
     */
    protected ?string $latestRemoteCommit = null;

    /**
     * Indicates if application is updated or not
     * @var bool
     */
    protected bool $applicationUpToDate = false;

    /**
     * Github constructor.
     */
    public function __construct() {
        $this->gitFolder = base_path('.git');
        $this
            ->checkIfApplicationIsLoadedFromGit()
            ->getLatestLocalCommit()
            ->getLatestRemoteCommit()
            ->checkIfApplicationIsUpToDate();
    }

    /**
     * Convert class to array
     * @return array
     */
    public function toArray() : array {
        return [
            'latest_version'    =>  $this->latestRemoteCommit,
            'local_version'     =>  $this->latestLocalCommit,
            'version'           =>  env('APP_VERSION'),
            'updated'           =>  $this->applicationUpToDate
        ];
    }

    /**
     * Check if application was loaded using `git clone` rather than just ZIP archive
     * @return Github|static|self|$this
     */
    protected function checkIfApplicationIsLoadedFromGit() : self {
        $lookingFor = ['COMMIT_EDITMSG', 'FETCH_HEAD', 'HEAD', 'ORIG_HEAD', 'config', 'description', 'index'];
        $actuallyGot = [];
        /**
         * @var SplFileInfo $file
         */
        foreach (File::files($this->gitFolder) as $file) {
            $fileName = str_replace($file->getPath() . DIRECTORY_SEPARATOR, '', $file->getRealPath());
            if (in_array($fileName, $lookingFor)) {
                $actuallyGot[] = $fileName;
            }
        }

        $this->isLoadedFromGithub = \count($lookingFor) === \count($actuallyGot);

        return $this;
    }

    /**
     * Get latest commit for the installed application
     * @return Github|static|self|$this
     */
    protected function getLatestLocalCommit() : self {
        $filePath = implode(DIRECTORY_SEPARATOR, [$this->gitFolder, 'refs', 'heads', 'master']);
        if (! File::exists($filePath)) {
            $this->isLoadedFromGithub = false;
            return $this;
        }
        $this->latestLocalCommit = trim(Arr::first(file($filePath)));
        return $this;
    }

    /**
     * Get latest commit from the github repository
     * @return Github|static|self|$this
     */
    protected function getLatestRemoteCommit() : self {
        $client = new Client;
        $response = Cache::remember('gitlab:commits', now()->addHour(), function() use ($client) {
            return json_decode($client->get($this->githubAPIUrl)->getBody()->getContents(), true);
        });
        $latestCommit = Arr::first($response);
        $this->latestRemoteCommit = $latestCommit['sha'];
        return $this;
    }

    /**
     * Check if application updated based on latest local and remote commit sha
     * @return Github|static|self|$this
     */
    protected function checkIfApplicationIsUpToDate() : self {
        $this->applicationUpToDate = trim($this->latestLocalCommit) === trim($this->latestRemoteCommit);
        return $this;
    }

}
