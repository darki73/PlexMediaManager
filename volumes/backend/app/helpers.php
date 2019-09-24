<?php

if (! function_exists('camel_to_underscore')) {

    /**
     * Convert camel case string to underscore
     * @param string $string
     * @return string
     */
    function camel_to_underscore(string $string) : string {
        preg_match_all('!([A-Z][A-Z0-9]*(?=$|[A-Z][a-z0-9])|[A-Za-z][a-z0-9]+)!', $string, $matches);
        $return = $matches[0];
        foreach ($return as &$match) {
            $match = $match === strtoupper($match) ? strtolower($match) : lcfirst($match);
        }
        return implode('_', $return);
    }

}

if (! function_exists('array_keys_between')) {

    function array_keys_between(array $array, int $starting, int $ending, bool $inclusive = true) : array {
        $data = [];

        if (!$inclusive) {
            ++$starting;
            --$ending;
        }
        foreach ($array as $key => $value) {
            if ((int) $key >= $starting && (int) $key <= $ending) {
                $data[$key] = $value;
            }
        }

        return $data;
    }

}

if (! function_exists('create_lostfilm_title')) {
    /**
     * Format series name to be valid for LostFilm.tv
     * @param string $seriesName
     * @return string
     */
    function create_lostfilm_title(string $seriesName) : string {
        return trim(preg_replace('/\(\d{4}\)/', '', $seriesName));
    }
}

if (! function_exists('fix_lostfilm_audio_tracks')) {

    function fix_lostfilm_audio_tracks(string $file) {
        $mediaInfo = new \Mhor\MediaInfo\MediaInfo();
        $mediaInfo->setConfig('use_oldxml_mediainfo_output_format', true);
        $mediaInfoContainer = $mediaInfo->getInfo($file);
        $audios = $mediaInfoContainer->getAudios();

        $commands = [];

        if (count($audios) === 2) {

            $ids = [
                'prefix'    =>  null,
                'tracks'    =>  []
            ];

            /**
             * @var \Mhor\MediaInfo\Type\Audio $audio
             */
            foreach ($audios as $index => $audio) {
                if ($audio->get('language') === null) {
                    /**
                     * @var \Mhor\MediaInfo\Attribute\Mode $mode
                     */
                    $mode = $audio->get('unique_id');
                    if ($mode === null) {
                        $ids['prefix'] = 'track:a';
                        $mode = $audio->get('id');
                    } else {
                        $ids['prefix'] = 'track:=';
                    }
                    $ids['tracks'][$index] = $mode->getFullName();
                }
            }

            if (isset($ids['tracks'])) {
                if (count($ids['tracks']) === 2) {
                    $commands[] = sprintf('mkvpropedit "%s" --edit %s%s --set language=rus --edit %s%s --set language=eng', $file, $ids['prefix'], $ids['tracks'][0], $ids['prefix'], $ids['tracks'][1]);
                } else if (count($ids['tracks']) !== 0) {
                    $missed[] = [
                        'step'      =>  'IDS Count',
                        'expected'  =>  2,
                        'got'       =>  count($ids['tracks']),
                        'file'      =>  $file
                    ];
                }
            }
        } else {
            $missed[] = [
                'step'      =>  'Audio Count',
                'expected'  =>  2,
                'got'       =>  count($audios),
                'file'      =>  $file
            ];
        }

        foreach ($commands as $command) {
            shell_exec($command);
        }
    }

}
