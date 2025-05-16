<?php
/**
 * Unit tests for the Announcements module.
 *
 * @package ThemeIsleSDK
 */

use ThemeisleSDK\Modules\Announcements;

class Announcements_Test extends WP_UnitTestCase {
	protected static $admin_id;
	/** @var Announcements */
	private $announcements;

	public static function wpSetUpBeforeClass( \WP_UnitTest_Factory $factory ) {
		self::$admin_id = $factory->user->create( [ 'role' => 'administrator' ] );
	}

	public static function wpTearDownAfterClass() {
		self::delete_user( self::$admin_id );
	}

	public function set_up() {
		parent::set_up();
		wp_set_current_user( self::$admin_id );
		$this->announcements = new Announcements();
	}

	public function tear_down() {
		remove_all_filters( 'themeisle_sdk_current_date' );
		remove_all_filters( 'themeisle_sdk_current_time' );
		remove_all_filters( 'themeisle_sdk_blackfriday_data' );

		// Reset private static flag
		$ref  = new \ReflectionClass( Announcements::class );
		$prop = $ref->getProperty( 'notice_loaded' );
		$prop->setAccessible( true );
		$prop->setValue( false );

		parent::tear_down();
	}

	public function test_can_show_notice_without_dismiss() {
		$today = new DateTime( '2025-03-10' );
		delete_user_meta( self::$admin_id, 'themeisle_sdk_dismissed_notice_black_friday' );
		$this->assertTrue(
			$this->announcements->can_show_notice( $today, self::$admin_id ),
			'Notice should show when user has not dismissed.'
		);
	}

	public function test_can_show_notice_when_dismissed_same_year() {
		$today = new DateTime( '2025-03-10' );
		update_user_meta(
			self::$admin_id,
			'themeisle_sdk_dismissed_notice_black_friday',
			strtotime( '2025-01-01' )
		);
		$this->assertFalse(
			$this->announcements->can_show_notice( $today, self::$admin_id ),
			'Notice should not show when dismissed this year.'
		);
	}

	public function test_get_black_friday_dates_and_duration() {
		$start = $this->announcements->get_start_date( new DateTime( '2025-01-01' ) );
		$this->assertEquals( '2025-11-24', $start->format( 'Y-m-d' ) );
		
		$end = $this->announcements->get_end_date( $start );
		$this->assertEquals( '2025-12-01 23:59:59', $end->format( 'Y-m-d H:i:s' ) );

		// check sale window boundaries
		$this->assertFalse( $this->announcements->is_black_friday_sale( new DateTime( '2025-11-23' ) ) );
		$this->assertTrue( $this->announcements->is_black_friday_sale( new DateTime( '2025-11-24' ) ) );
		$this->assertTrue( $this->announcements->is_black_friday_sale( new DateTime( '2025-11-28' ) ) );
		$this->assertTrue( $this->announcements->is_black_friday_sale( new DateTime( '2025-12-01' ) ) );
		$this->assertFalse( $this->announcements->is_black_friday_sale( new DateTime( '2025-12-02' ) ) );
	}

	public function test_get_remaining_time_for_event() {
		$base = new DateTime( '2025-12-01 10:00:00' );
		add_filter(
			'themeisle_sdk_current_date',
			function() use ( $base ) {
				return $base;
			}
		);

		$end  = new DateTime( '2025-12-02 00:00:00' );
		$diff = $this->announcements->get_remaining_time_for_event( $end );
		$this->assertNotEmpty( $diff );
		$this->assertStringContainsString( 'hour', $diff );
	}

	public function test_load_announcements_registers_hooks_during_sale() {
		add_filter(
			'themeisle_sdk_current_date',
			function() {
				return new DateTime( '2025-11-25' );
			}
		);

		$this->announcements->load_announcements();
		$this->assertTrue(
			has_action( 'admin_notices', [ $this->announcements, 'black_friday_notice_render' ] ) > 0
		);
		$this->assertTrue(
			has_action( 'wp_ajax_themeisle_sdk_dismiss_black_friday_notice', [ $this->announcements, 'disable_notification_ajax' ] ) > 0
		);
	}

	public function test_load_announcements_skips_outside_sale() {
		add_filter(
			'themeisle_sdk_current_date',
			function() {
				return new DateTime( '2025-10-15' );
			}
		);

		$this->announcements->load_announcements();
		$this->assertFalse(
			has_action( 'admin_notices', [ $this->announcements, 'black_friday_notice_render' ] )
		);
	}
}
