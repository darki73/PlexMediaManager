<?php namespace App\Console\Commands\PlexMediaManager;


use App\Console\Commands\DumperCommand;
use Spatie\DbDumper\Exceptions\CannotSetParameter;

/**
 * Class DumpRest
 * @package App\Console\Commands\PlexMediaManager
 */
class DumpRest extends DumperCommand {

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pmm:dump';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Dump other necessary tables to SQL file';

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
     * @throws CannotSetParameter
     */
    public function handle() : void {
        $saveTo = storage_path(implode(DIRECTORY_SEPARATOR, ['dumps', 'pmm.sql.gz']));
        $this->dump($saveTo, [
            'creators',
            'crew_members',
            'genres',
            'guest_stars',
            'networks',
            'production_companies',
            'production_countries',
        ]);
    }

}
