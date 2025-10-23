<?php
/**
 * API Client tests.
 *
 * @package Plugin_Curator
 */

namespace RFPM\Tests;

use RFPM\API_Client;
use RFPM\Cache_Manager;
use WP_Mock\Tools\TestCase;

/**
 * Test API_Client class.
 */
class Test_API_Client extends TestCase {

    /**
     * API client instance.
     *
     * @var API_Client
     */
    private $api_client;

    /**
     * Cache manager mock.
     *
     * @var \Mockery\MockInterface
     */
    private $cache_manager;

    /**
     * Set up test.
     */
    public function setUp(): void {
        \WP_Mock::setUp();
        $this->cache_manager = \Mockery::mock( Cache_Manager::class );
        $this->api_client    = new API_Client( $this->cache_manager );
    }

    /**
     * Tear down test.
     */
    public function tearDown(): void {
        \WP_Mock::tearDown();
    }

    /**
     * Test build_api_response method.
     */
    public function test_build_api_response() {
        $plugins = array(
            (object) array( 'slug' => 'plugin-1' ),
            (object) array( 'slug' => 'plugin-2' ),
        );

        \WP_Mock::onFilter( 'rfpm_api_response' )
            ->with( \Mockery::type( \stdClass::class ), $plugins )
            ->reply( function( $response ) {
                return $response;
            } );

        $result = $this->api_client->build_api_response( $plugins );

        $this->assertInstanceOf( \stdClass::class, $result );
        $this->assertEquals( $plugins, $result->plugins );
        $this->assertIsArray( $result->info );
        $this->assertEquals( 2, $result->info['results'] );
    }

    /**
     * Test fetch_plugins with empty array.
     */
    public function test_fetch_plugins_empty() {
        \WP_Mock::expectActionAdded( 'rfpm_plugins_fetched', \Mockery::any(), \Mockery::any() );

        \WP_Mock::expectAction( 'rfpm_plugins_fetched', array(), array() );

        $result = $this->api_client->fetch_plugins( array() );

        $this->assertIsArray( $result );
        $this->assertEmpty( $result );
    }
}

