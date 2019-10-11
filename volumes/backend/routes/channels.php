<?php

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('account.{id}', function(User $user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('account.admins', function(User $user) {
    return $user->hasRole('administrator');
});
