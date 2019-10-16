<?php namespace Tests\Unit\Classes\Github;

use Tests\TestCase;
use App\Classes\Github\Github;

/**
 * Class GithubTest
 * @package Tests\Unit\Classes\Github
 */
class GithubTest extends TestCase {

    /**
     * Test that `latest_version` is of type String or of type Null
     * @return void
     */
    public function testLatestVersionIsStringOrNull() : void {
        $provider = $this->provider();
        $this->assertTrue(is_string($provider['latest_version']) || is_null($provider['latest_version']));
    }

    /**
     * Test that `local_version` is of type String or of type Null
     * @return void
     */
    public function testLocalVersionIsStringOrNull() : void {
        $provider = $this->provider();
        $this->assertTrue(is_string($provider['local_version']) || is_null($provider['local_version']));
    }

    /**
     * Test that `version` is of type String or of type Null
     * @return void
     */
    public function testVersionIsStringOrNull() : void {
        $provider = $this->provider();
        $this->assertTrue(is_string($provider['version']) || is_null($provider['version']));
    }

    /**
     * Test that `updated` is of type Boolean
     * @return void
     */
    public function testUpdatedIsBoolean() : void {
        $provider = $this->provider();
        $this->assertIsBool($provider['updated']);
    }

    /**
     * Github Data Provider
     * @return array
     */
    protected function provider() : array {
        return (new Github)->toArray();
    }

}
