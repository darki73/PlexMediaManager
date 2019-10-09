<?php namespace App\Console\Commands\PlexMediaManager;

use Illuminate\Console\Command;
use Illuminate\Console\ConfirmableTrait;

/**
 * Class SocketsKeysCommand
 * @package App\Console\Commands\PlexMediaManager
 */
class SocketsKeysCommand extends Command {

    use ConfirmableTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pmm:socket-keys
                    {--show : Display the key instead of modifying files}
                    {--force : Force the operation to run when in production}';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set the websockets keys';
    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle() {
        if ($this->option('show')) {
            $this->line('<comment>Public key: ' . env('WS_APP_KEY') . '</comment>');
            $this->line('<comment>Secret key: ' . env('WS_APP_SECRET') . '</comment>');
            return;
        } else {
            $publicKey = $this->generatePublicKey();
            $secretKey = $this->generateSecretKey();

            $publicKeySetStatus = $this->setKeyInEnvironmentFile($publicKey, 'WS_APP_KEY');
            $secretKeySetStatus = $this->setKeyInEnvironmentFile($secretKey, 'WS_APP_SECRET');

            if ($publicKeySetStatus) {
                $this->info('Public socket key has been updated.');
            }
            if ($secretKeySetStatus) {
                $this->info('Secret socket key has been updated.');
            }

            if (!$publicKeySetStatus && !$secretKeySetStatus) {
                $this->info('Socket keys we not updated as they are already set.');
            }
        }
    }


    /**
     * Generate public key
     * @return string
     */
    protected function generatePublicKey() : string {
        return generateUUIDVersion5(env('APP_URL'), true);
    }

    /**
     * Generate secret key
     * @return string
     */
    protected function generateSecretKey() : string {
        return generateUUIDVersion5(hash('sha256', env('APP_URL')), true);
    }

    /**
     * Set the application key in the environment file.
     * @param string $key
     * @param string $keyType
     * @return bool
     */
    protected function setKeyInEnvironmentFile(string $key, string $keyType) : bool {
        $currentKey = env($keyType);
        if (strlen($currentKey) !== 0) {
            if (! $this->option('force')) {
                return false;
            }
        }
        $this->writeNewEnvironmentFileWith($key, $keyType);
        return true;
    }
    /**
     * Write a new environment file with the given key.
     * @param string $keyValue
     * @param string $key
     * @return void
     */
    protected function writeNewEnvironmentFileWith(string $keyValue, string $key) : void {
        file_put_contents($this->laravel->environmentFilePath(), str_replace(
            $this->keyReplacementPattern($key),
            $key . '=' . $keyValue,
            file_get_contents($this->laravel->environmentFilePath())
        ));
    }

    /**
     * Get key replacement pattern
     * @param string $key
     * @return string
     */
    protected function keyReplacementPattern(string $key) : string {
        return sprintf('%s=%s', $key, env($key));
    }
}
