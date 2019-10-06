<?php namespace App\Console\Commands\Account;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

/**
 * Class CreateAdministrator
 * @package App\Console\Commands\Account
 */
class CreateAdministrator extends Command {

    /**
     * The name and signature of the console command
     * @var string
     */
    protected $signature = 'administrator:create';

    /**
     * The console command description
     * @var string
     */
    protected $description = 'Create new administrator user';

    /**
     * CreateAdministrator constructor.
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * Execute command
     * @return void
     */
    public function handle() : void {
        $username = $this->ask('Please provide `Username` for the Administrator');

        if (User::where('username', '=', $username)->exists()) {
            $this->warn('Administrator with provided Username already exists');
            return;
        }

        $email = $this->ask('Please provide `E-Mail` for the Administrator');

        if (User::where('email', '=', $email)->exists()) {
            $this->warn('Administrator with provided E-Mail already exists');
            return;
        }

        $password = $this->secret('Please provide `Password` for the Administrator');
        $passwordConfirmation = $this->secret('Please provide `Password Confirmation` for the Administrator');

        if ($password !== $passwordConfirmation) {
            $this->warn('Provided passwords do not match');
            return;
        }

        $password = Hash::make($password);

        try {
            $avatar = \Avatar::create($username)->getImageObject()->encode('png', 100);
            $avatarDirectory = storage_path(implode(DIRECTORY_SEPARATOR, ['app', 'public', 'avatars', $username]));
            if (!File::exists($avatarDirectory)) {
                File::makeDirectory($avatarDirectory, 0755, true);
            }
            \Storage::put(implode(DIRECTORY_SEPARATOR, ['public', 'avatars', $username, 'avatar.png']), (string) $avatar);
        } catch (\Exception $exception) {
            $this->error('Encountered error when tried to crate directory for user avatar. Message: ' . $exception->getMessage());
            return;
        }

        try {
            $user = User::create([
                'username'              =>  $username,
                'email'                 =>  $email,
                'password'              =>  $password,
                'email_verified_at'     =>  Carbon::now()->toDateTimeString()
            ]);

            /**
             * @var Role $role
             */
            foreach(Role::whereName('administrator')->get() as $role) {
                $user->assignRole($role);
            }

            $this->info('Successfully created administrator: ' . $email);
        } catch (\Exception $exception) {
            $this->warn('Unable to create user: ' . $exception->getMessage());
        }

    }

}
