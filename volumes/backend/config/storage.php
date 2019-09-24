<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Preferred Drive
    |--------------------------------------------------------------------------
    |
    | This options controls the drive which is considered to be preferred
    | to write data to. Whenever this drive is considered to be usable,
    | all data will be written to this drive.
    */
    'preferred'         =>  env('STORAGE_PREFERRED_DRIVE', null),

    /*
    |--------------------------------------------------------------------------
    | Drive Write Threshold
    |--------------------------------------------------------------------------
    |
    | You can set a threshold for the amount of data written to the drives.
    | Upon reaching a certain threshold, drive will be considered unusable
    | and system will try to pick a different drive to write data to.
    |
    | Threshold can be percentage based or remaining disk size based.
    | When you use `percentage` based threshold, upon reaching a
    | certain percentage of free space left on the drive, the
    | next drive will be picked to write data to.
    */
    'threshold'         =>  [
        'value'         =>  (int) env('STORAGE_THRESHOLD', 500),
        'units'         =>  env('STORAGE_THRESHOLD_UNITS', 'GB'),
        'percentage'    =>  env('STORAGE_THRESHOLD_PERCENTAGE', false)
    ],

    /*
    |--------------------------------------------------------------------------
    | Drive Mounts
    |--------------------------------------------------------------------------
    |
    | You have to manually specify the mounts for the drives due to the fact
    | that if we are about to move data in the docker container, move speed
    | will be ridiculously slow, hence we must use the external mount
    | paths in order to fully utilize IO of the system.
    |
    | There are two types of mounts, one for `plex` and one for `torrent`.
    | Plex mount is responsible for different drives which are available
    | inside your Plex container.
    |
    | There is only one mount available for Torrent since data will not
    | be stored in there for a long time and will be moved to respective
    | Plex mounts.
    */
    'mounts'            =>  [
        'plex'          =>  [
            'hdd'       =>  '/docker/hdd/volumes/plex/media',
            'nas'       =>  '/docker/nas/volumes/plex/media',
        ],
        'torrent'       =>  '/docker/nas/volumes/qbittorrent/completed'
    ],

    /*
    |--------------------------------------------------------------------------
    | Allowed Extension
    |--------------------------------------------------------------------------
    |
    | This list defines types of media which will be allowed for processing
    | by the source parsers for series and movies.
    |
    | If you have media which has extension which is not on this list,
    | this media will be excluded from processing.
    */
    'process_only'      =>  [
        'avi',
        'mkv',
        'mp4',
        'm4v'
    ]
];
