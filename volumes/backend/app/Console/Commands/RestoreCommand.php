<?php namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

/**
 * Class SeriesRestore
 * @package App\Console\Commands\Series
 */
abstract class RestoreCommand extends Command {

    /**
     * Restore dump to database
     * @param string $dumpFile
     * @param string $targetFile
     * @return void
     */
    public function restore(string $dumpFile, string $targetFile) : void {
        if (File::exists($dumpFile)) {
            $this->info('Extracting dump from the archive...');
            shell_exec(sprintf('gunzip -c %s > %s', $dumpFile, $targetFile));
            $this->info('Extraction has been completed, starting the import now...' . PHP_EOL);
            shell_exec(sprintf(
                'mysql -u %s -p%s -h %s %s < %s',
                env('DB_USERNAME'),
                env('DB_PASSWORD'),
                env('DB_HOST'),
                env('DB_DATABASE'),
                $targetFile
            ));
            unlink($targetFile);
            $this->info('Successfully restored all related tables');
        }
    }


}
