<?php namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\DbDumper\Databases\MySql;
use Spatie\DbDumper\Compressors\GzipCompressor;
use Spatie\DbDumper\Exceptions\CannotSetParameter;

/**
 * Class DumperCommand
 * @package App\Console\Commands
 */
abstract class DumperCommand extends Command {

    /**
     * Dump data
     * @param string $path
     * @param array $tables
     * @return void
     * @throws CannotSetParameter
     */
    protected function dump(string $path, array $tables) : void {
        MySql::create()
            ->setDbName(env('DB_DATABASE'))
            ->setUserName(env('DB_USERNAME'))
            ->setPassword(env('DB_PASSWORD'))
            ->addExtraOption(sprintf('-h %s --no-create-info',
                env('DB_HOST'),
            ))
            ->includeTables($tables)
            ->useCompressor(new GzipCompressor)
            ->dumpToFile($path);
    }

}
