<?php
/**
 * Plugin Filter tests.
 *
 * @package Plugin_Curator
 */

namespace RFPM\Tests;

use RFPM\Plugin_Filter;
use RFPM\Remote_Source;
use RFPM\API_Client;
use RFPM\Cache_Manager;
use WP_Mock\Tools\TestCase;

/**
 * Test Plugin_Filter class.
 */
class Test_Plugin_Filter extends TestCase {

    /**
     * Plugin filter instance.
     *
     * @var Plugin_Filter
     */
    private $plugin_filter;

    /**
     * Remote source mock.
     *
     * @var \Mockery\MockInterface
     */
    private $remote_source;

    /**
     * API client mock.
     *
     * @var \Mockery\MockInterface
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
        $this->remote_source = \Mockery::mock( Remote_Source::class );
        $this->api_client    = \Mockery::mock( API_Client::class );
        $this->cache_manager = \Mockery::mock( Cache_Manager::class );
        $this->plugin_filter = new Plugin_Filter( $this->remote_source, $this->api_client, $this->cache_manager );
    }

    /**
     * Tear down test.
     */
    public function tearDown(): void {
        \WP_Mock::tearDown();
    }

    /**
     * Test filter ignores non-featured requests.
     */
    public function test_filter_ignores_non_query_plugins() {
        $result = $this->plugin_filter->filter_featured_plugins( false, 'plugin_information', new \stdClass() );

        $this->assertFalse( $result );
    }

    /**
     * Test filter ignores non-featured browse types.
     */
    public function test_filter_ignores_non_featured() {
        $args = new \stdClass();
        $args->browse = 'popular';

        $result = $this->plugin_filter->filter_featured_plugins( false, 'query_plugins', $args );

        $this->assertFalse( $result );
    }

    /**
     * Test filter returns cached response.
     */
    public function test_filter_returns_cached() {
        $args = new \stdClass();
        $args->browse = 'featured';

        $cached_response = new \stdClass();
        $cached_response->plugins = array();

        $this->cache_manager
            ->shouldReceive( 'get' )
            ->once()
            ->with( Cache_Manager::PLUGINS_CACHE_KEY )
            ->andReturn( $cached_response );

        $result = $this->plugin_filter->filter_featured_plugins( false, 'query_plugins', $args );

        $this->assertEquals( $cached_response, $result );
    }

    /**
     * Test filter falls back when no slugs.
     */
    public function test_filter_fallback_no_slugs() {
        $args = new \stdClass();
        $args->browse = 'featured';

        $this->cache_manager
            ->shouldReceive( 'get' )
            ->once()
            ->with( Cache_Manager::PLUGINS_CACHE_KEY )
            ->andReturn( false );

        $this->remote_source
            ->shouldReceive( 'get_slugs' )
            ->once()
            ->andReturn( false );

        $result = $this->plugin_filter->filter_featured_plugins( false, 'query_plugins', $args );

        $this->assertFalse( $result );
    }
}

