/**
 * WordPress dependencies
 */
import { test, expect } from '@wordpress/e2e-test-utils-playwright';

/**
 * Internal dependencies
 */
import { blockRequests } from '../utils';

test.describe( 'Admin', () => {

	test( 'check About Us page', async({ page }) => {
		await page.goto( '/wp-admin/admin.php?page=themeisle-sdk' );
		await page.locator( '#toplevel_page_themeisle-sdk' ).getByRole( 'link', { name: 'About Us' }).click();
		await page.waitForSelector( '#ti-sdk-about' );

		// Header components are visible.
		await expect(  page.locator( '.head img' ) ).toBeVisible();
		await expect( page.locator( 'p' ).filter({ hasText: 'by Themeisle' }).getByRole( 'link' ) ).toBeVisible();

		// Newsletter form is visible.
		await expect( page.getByPlaceholder( 'Your email address' ) ).toBeVisible();
		await expect( page.getByRole( 'button', { name: 'Sign me up' }) ).toBeVisible();

		// We have product install shortcuts.
		await expect( page.locator( '.product-card' ).count() ).resolves.toBeGreaterThan( 0 );

		// We should have some uninstalled products.
		await expect( page.locator( '.product-card' ).filter({ has: page.locator( '.not-installed' ) }).count() ).resolves.toBeGreaterThan( 0 );

		// Landing Kit link is visible.
		await expect( page.getByRole( 'link', { name: 'Learn More' }) ).toBeVisible();
	});

	test( 'check survey and tracking script loading (on Visualizer)', async({ page, admin, context }) => {
		await blockRequests( context );
		await admin.visitAdminPage( 'admin.php?page=visualizer' );

		await expect( page.locator( 'script#themeisle_sdk_survey_script-js' ) ).toHaveCount( 1 );
		await expect( page.locator( 'script#themeisle_sdk_tracking_script-js' ) ).toHaveCount( 1 );
		await expect( page.locator( 'script#themeisle_sdk_tracking_script-js-extra' ) ).toHaveCount( 1 );

		const tiTelemetryDefined = await page.evaluate( () => {
			return 'undefined' !== typeof window.tiTelemetry;
		});
		expect( tiTelemetryDefined ).toBe( true );
	});

	test( 'check window.tiTrk functionality', async({ page, admin, context }) => {
		await admin.visitAdminPage( 'admin.php?page=visualizer' );
		await blockRequests( context );

		// Check if window.tiTrk exists
		const tiTrkExists = await page.evaluate( () => {
			return 'undefined' !== typeof window.tiTrk;
		});
		expect( tiTrkExists ).toBe( true );

		// Check if window.tiTrk has the expected methods
		const tiTrkMethods = await page.evaluate( () => {
			return {
				hasWithMethod: 'function' === typeof window.tiTrk.with,
				hasUploadEventsMethod: 'function' === typeof window.tiTrk.uploadEvents,
				hasStartMethod: 'function' === typeof window.tiTrk.start,
				hasStopMethod: 'function' === typeof window.tiTrk.stop
			};
		});
		expect( tiTrkMethods ).toEqual({
			hasWithMethod: true,
			hasUploadEventsMethod: true,
			hasStartMethod: true,
			hasStopMethod: true
		});

		// Test adding an event
		const eventAdded = await page.evaluate( () => {
			const testEvent = {
				slug: 'visualizer',
				action: 'test-action',
				feature: 'test-feature'
			};
			window.tiTrk.with( 'visualizer' ).add( testEvent );
			return 0 < window.tiTrk.events.size;
		});
		expect( eventAdded ).toBe( true );

		// Test uploading events (this will clear the events)
		await page.evaluate( () => {
			window.tiTrk.uploadEvents();
		});

		// Check if events were cleared after uploading
		const eventsCleared = await page.evaluate( () => {
			return 0 === window.tiTrk.events.size;
		});
		expect( eventsCleared ).toBe( true );
	});
});
