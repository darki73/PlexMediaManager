<?php namespace Tests\Unit\Classes\Integrations;

use App\Classes\Integrations\Message;
use App\Classes\Integrations\NotificationsManager;
use Tests\TestCase;

/**
 * Class NotificationsManagerTest
 * @package Tests\Unit\Classes\Integrations
 */
class NotificationsManagerTest extends TestCase {

    /**
     * Check for the valid case (the way string is typed in) for the Series Informer
     * @return void
     */
    public function testCheckSeriesInformerValueIsInCorrectCasing() : void {
        $this->assertEquals('Series', NotificationsManager::SERIES_INFORMER);
    }

    /**
     * Check for the valid case (the way string is typed in) for the Movies Informer
     * @return void
     */
    public function testCheckMoviesInformerValueIsInCorrectCasing() : void {
        $this->assertEquals('Movies', NotificationsManager::MOVIES_INFORMER);
    }

    /**
     * Check for the valid case (the way string is typed in) for the Music Informer
     * @return void
     */
    public function testCheckMusicInformerValueIsInCorrectCasing() : void {
        $this->assertEquals('Music', NotificationsManager::MUSIC_INFORMER);
    }

}
