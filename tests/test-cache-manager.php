<?php
/**
 * Cache Manager tests.
 *
 * @package Plugin_Curator
 */

namespace RFPM\Tests;

use RFPM\Cache_Manager;
use WP_Mock\Tools\TestCase;

/**
 * Test Cache_Manager class.
 */
class Test_Cache_Manager extends TestCase {

    /**
     * Cache manager instance.
     *
     * @var Cache_Manager
     */
    private $cache_manager;

    /**
     * Set up test.
     */
    public function setUp(): void {
        \WP_Mock::setUp();
        $this->cache_manager = new Cache_Manager();
    }

    /**
     * Tear down test.
     */
    public function tearDown(): void {
        \WP_Mock::tearDown();
    }

    /**
     * Test get method.
     */
    public function test_get() {
        \WP_Mock::userFunction( 'get_transient' )
            ->once()
            ->with( 'test_key' )
            ->andReturn( 'test_value' );

        $result = $this->cache_manager->get( 'test_key' );

        $this->assertEquals( 'test_value', $result );
    }

    /**
     * Test set method.
     */
    public function test_set() {
        \WP_Mock::userFunction( 'get_option' )
            ->once()
            ->with( 'rfpm_cache_duration', 6 * HOUR_IN_SECONDS )
            ->andReturn( 6 * HOUR_IN_SECONDS );

        \WP_Mock::onFilter( 'rfpm_cache_duration' )
            ->with( 6 * HOUR_IN_SECONDS )
            ->reply( 6 * HOUR_IN_SECONDS );

        \WP_Mock::userFunction( 'set_transient' )
            ->once()
            ->with( 'test_key', 'test_value', 6 * HOUR_IN_SECONDS )
            ->andReturn( true );

        $result = $this->cache_manager->set( 'test_key', 'test_value' );

        $this->assertTrue( $result );
    }

    /**
     * Test delete method.
     */
    public function test_delete() {
        \WP_Mock::userFunction( 'delete_transient' )
            ->once()
            ->with( 'test_key' )
            ->andReturn( true );

        $result = $this->cache_manager->delete( 'test_key' );

        $this->assertTrue( $result );
    }

    /**
     * Test clear_all method.
     */
    public function test_clear_all() {
        \WP_Mock::userFunction( 'delete_transient' )
            ->times( 3 )
            ->andReturn( true );

        $result = $this->cache_manager->clear_all();

        $this->assertTrue( $result );
    }

    /**
     * Test get_stats method.
     */
    public function test_get_stats() {
        \WP_Mock::userFunction( 'get_transient' )
            ->times( 3 )
            ->andReturn( false );

        \WP_Mock::userFunction( 'get_option' )
            ->times( 3 )
            ->andReturn( 0 );

        \WP_Mock::userFunction( 'maybe_serialize' )
            ->times( 0 );

        $stats = $this->cache_manager->get_stats();

        $this->assertIsArray( $stats );
        $this->assertArrayHasKey( 'slugs', $stats );
        $this->assertArrayHasKey( 'plugins', $stats );
        $this->assertArrayHasKey( 'partial', $stats );
    }
}

