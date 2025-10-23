<?php
/**
 * Remote Source tests.
 *
 * @package Plugin_Curator
 */

namespace RFPM\Tests;

use RFPM\Remote_Source;
use RFPM\Cache_Manager;
use WP_Mock\Tools\TestCase;

/**
 * Test Remote_Source class.
 */
class Test_Remote_Source extends TestCase {

    /**
     * Remote source instance.
     *
     * @var Remote_Source
     */
    private $remote_source;

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
        $this->remote_source = new Remote_Source( $this->cache_manager );
    }

    /**
     * Tear down test.
     */
    public function tearDown(): void {
        \WP_Mock::tearDown();
    }

    /**
     * Test get_slugs returns cached data.
     */
    public function test_get_slugs_returns_cached() {
        $cached_slugs = array( 'plugin-1', 'plugin-2' );

        $this->cache_manager
            ->shouldReceive( 'get' )
            ->once()
            ->with( Cache_Manager::SLUGS_CACHE_KEY )
            ->andReturn( $cached_slugs );

        $result = $this->remote_source->get_slugs();

        $this->assertEquals( $cached_slugs, $result );
    }

    /**
     * Test get_slugs with empty remote URL.
     */
    public function test_get_slugs_empty_url() {
        $this->cache_manager
            ->shouldReceive( 'get' )
            ->once()
            ->with( Cache_Manager::SLUGS_CACHE_KEY )
            ->andReturn( false );

        \WP_Mock::userFunction( 'get_option' )
            ->once()
            ->with( 'rfpm_remote_url', '' )
            ->andReturn( '' );

        $result = $this->remote_source->get_slugs();

        $this->assertFalse( $result );
    }

    /**
     * Test test_connection method.
     */
    public function test_connection_empty_url() {
        \WP_Mock::userFunction( 'get_option' )
            ->once()
            ->with( 'rfpm_remote_url', '' )
            ->andReturn( '' );

        \WP_Mock::userFunction( '__' )
            ->andReturnUsing( function( $text ) {
                return $text;
            } );

        $result = $this->remote_source->test_connection();

        $this->assertIsArray( $result );
        $this->assertFalse( $result['success'] );
        $this->assertArrayHasKey( 'message', $result );
    }
}

