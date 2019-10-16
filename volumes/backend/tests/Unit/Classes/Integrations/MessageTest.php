<?php namespace Tests\Unit\Classes\Integrations;

use Tests\TestCase;
use App\Classes\Integrations\Message;

/**
 * Class MessageTest
 * @package Tests\Unit\Classes\Integrations
 */
class MessageTest extends TestCase {

    /**
     * Test that the timestamp will always be populated upon class creation
     * @return void
     */
    public function testTimestampIsSetWhenClassIsCreated() : void {
        $this->assertNotNull($this->emptyClass()->getTimestamp());
    }

    /**
     * Test that 'title' is set to null upon class creation
     * @return void
     */
    public function testTitleIsSetToNullWhenClassIsCreated() : void {
        $this->assertNull($this->emptyClass()->getTitle());
    }

    /**
     * Test that 'message' is set to null upon class creation
     * @return void
     */
    public function testMessageIsSetToNullWhenClassIsCreated() : void {
        $this->assertNull($this->emptyClass()->getMessage());
    }

    /**
     * Test that 'informer' is set to null upon class creation
     * @return void
     */
    public function testInformerIsSetToNullWhenClassIsCreated() : void {
        $this->assertNull($this->emptyClass()->getInformer());
    }

    /**
     * Test that 'color' is set to null upon class creation
     * @return void
     */
    public function testColorIsSetToNullWhenClassIsCreated() : void {
        $this->assertNull($this->emptyClass()->getColor());
    }

    /**
     * Test that 'description' is set to null upon class creation
     * @return void
     */
    public function testDescriptionIsSetToNullWhenClassIsCreated() : void {
        $this->assertNull($this->emptyClass()->getDescription());
    }

    /**
     * Test that 'url' is set to null upon class creation
     * @return void
     */
    public function testUrlIsSetToNullWhenClassIsCreated() : void {
        $this->assertNull($this->emptyClass()->getUrl());
    }

    /**
     * Test that 'thumbnail' is set to null upon class creation
     * @return void
     */
    public function testThumbnailIsSetToNullWhenClassIsCreated() : void {
        $this->assertNull($this->emptyClass()->getThumbnail());
    }

    /**
     * Test that there are exact (required) number of items returned by the `toArray()` method
     * @return void
     */
    public function testToArrayMethodReturnsRequiredNumberOfItems() : void {
        $this->assertCount(8, $this->emptyClass()->toArray());
    }

    /**
     * Test that all of the required keys are present on the result returned by the `toArray()` method
     * @return void
     */
    public function testToArrayReturnsNecessaryKeys() : void {
        $required = [
            'title',
            'description',
            'message',
            'informer',
            'color',
            'url',
            'thumbnail',
            'timestamp',
        ];
        $toArray = $this->emptyClass()->toArray();
        foreach ($required as $key) {
            $this->assertArrayHasKey($key, $toArray);
        }
    }

    /**
     * Test that seriesDownloadStart method generates correct title
     * @return void
     */
    public function testSeriesDownloadStartMethodTitleIsInTheCorrectFormatAndMatchesWithDummySeriesPresent() : void {
        $message = Message::seriesDownloadStart($this->dummySeries());
        $this->assertEquals('13 Reasons Why (2017)', $message->getTitle());
    }

    /**
     * Test that seriesDownloadStart method generates correct message
     * @return void
     */
    public function testSeriesDownloadStartMethodMessageIsInTheCorrectFormatAndMatchesWithDummySeriesPresent() : void {
        $message = Message::seriesDownloadStart($this->dummySeries());
        $this->assertEquals('Started download procedure for `13 Reasons Why (2017)`', $message->getMessage());
    }

    /**
     * Test that seriesDownloadStart method generates correct informer string
     * @return void
     */
    public function testSeriesDownloadStartMethodInformerIsInTheCorrectFormatAndMatchesWithDummySeriesPresent() : void {
        $message = Message::seriesDownloadStart($this->dummySeries());
        $this->assertEquals('Series', $message->getInformer());
    }

    /**
     * Test that seriesDownloadStart method sets the color
     * @return void
     */
    public function testSeriesDownloadStartMethodColorIsInTheCorrectFormatWithDummySeriesPresent() : void {
        $message = Message::seriesDownloadStart($this->dummySeries());
        $this->assertIsInt($message->getColor());
    }

    /**
     * Test that seriesDownloadStart method generates the correct description
     * @return void
     */
    public function testSeriesDownloadStartMethodDescriptionIsInTheCorrectFormatAndMatchesWithDummySeriesPresent() : void {
        $message = Message::seriesDownloadStart($this->dummySeries());
        $this->assertEquals('After a teenage girl\'s perplexing suicide, a classmate receives a series of tapes that unravel the mystery of her tragic choice.', $message->getDescription());
    }

    /**
     * Test that seriesDownloadStart method generates the correct url
     * @return void
     */
    public function testSeriesDownloadStartMethodUrlIsInTheCorrectFormatAndMatchesWithDummySeriesPresent() : void {
        $message = Message::seriesDownloadStart($this->dummySeries());
        $this->assertEquals('https://www.netflix.com/title/80117470', $message->getUrl());
    }

    /**
     * Test that seriesDownloadStart method generates the correct thumbnail url
     * @return void
     */
    public function testSeriesDownloadStartMethodThumbnailIsInTheCorrectFormatAndMatchesWithDummySeriesPresent() : void {
        $series = $this->dummySeries();
        $message = Message::seriesDownloadStart($series);
        $this->assertEquals(sprintf('https://%s/storage/images/series/%d/global/w185/%s', env('APP_URL'), $series->id, $series->poster), $message->getThumbnail());
    }

    /**
     * Test that seriesDownloadStart method converted to the array returns correct data
     * @return void
     */
    public function testSeriesDownloadStartMethodReturnsCorrectDataWhenConvertedToArray() : void {
        $series = $this->dummySeries();
        $message = Message::seriesDownloadStart($series);
        $array = $message->toArray();

        $required = [
            'title',
            'description',
            'message',
            'informer',
            'color',
            'url',
            'thumbnail',
            'timestamp',
        ];
        $toArray = $this->emptyClass()->toArray();
        foreach ($required as $key) {
            $this->assertArrayHasKey($key, $toArray);
        }

        $this->assertEquals('13 Reasons Why (2017)', $array['title']);
        $this->assertEquals('After a teenage girl\'s perplexing suicide, a classmate receives a series of tapes that unravel the mystery of her tragic choice.', $array['description']);
        $this->assertEquals('Started download procedure for `13 Reasons Why (2017)`', $array['message']);
        $this->assertEquals('Series', $array['informer']);
        $this->assertIsInt($array['color']);
        $this->assertEquals('https://www.netflix.com/title/80117470', $array['url']);
        $this->assertEquals(sprintf('https://%s/storage/images/series/%d/global/w185/%s', env('APP_URL'), $series->id, $series->poster), $array['thumbnail']);

    }

    /**
     * Test that upon message creation, instance of Message is returned
     * @return void
     */
    public function testMessageClassInstanceIsReturnedUponMessageCreation() : void {
        $this->assertInstanceOf(Message::class, Message::seriesDownloadStart($this->dummySeries()));
    }

    /**
     * Create new instance of Message
     * @return Message
     */
    protected function emptyClass() : Message {
        return new Message;
    }

}
