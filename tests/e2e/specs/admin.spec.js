/**
 * WordPress dependencies
 */
import { test, expect } from '@wordpress/e2e-test-utils-playwright';

test.describe( 'Admin', () => {

    test( 'check About Us page', async ( { page} ) => {
        await page.goto( '/wp-admin/admin.php?page=themeisle-sdk' );
        await page.locator( '#toplevel_page_themeisle-sdk' ).getByRole( 'link', { name: 'About Us' }).click();
        await page.waitForSelector( '#ti-sdk-about' );

        // Header components are visible.
        await expect(  page.waitForSelector( '.head img' ) ).toBeVisible();
        await expect( page.locator( 'p' ).filter({ hasText: 'by Themeisle' }).getByRole('link') ).toBeVisible();

        // Newsletter form is visible.
        await expect( page.getByPlaceholder( 'Your email address' ) ).toBeVisible();
        await expect( page.getByRole( 'button', { name: 'Sign me up' }) ).toBeVisible();

        // We have product install shortcuts.
        await expect( page.locator( '.product-card' ).count() ).resolves.toBeGreaterThan( 0 );

        // We should some uninstalled products.
        await expect( page.locator( '.product-card' ).filter({ has: page.locator( '.not-installed' ) }).count() ).resolves.toBeGreaterThan( 0 );

        // Landing Kit link is visible.
        await expect( page.getByRole( 'link', { name: 'Learn More' }) ).toBeVisible();
    });
});
