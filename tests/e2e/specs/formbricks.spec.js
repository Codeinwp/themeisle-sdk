/**
 * WordPress dependencies
 */
import { test, expect } from '@wordpress/e2e-test-utils-playwright';

test.describe( 'Formbricks', () => {

	test( 'check initialization', async({ page, admin }) => {
		await admin.visitAdminPage( 'admin.php?page=themeisle-sdk&formbricksDebug=true' );

		page.on( 'console', msg => {
			const text = msg.text();
			if ( text.includes( 'Formbricks' ) && text.includes( 'Warning' ) ) {

				// Fail on warning.
				expect( true, `Formbricks warning found: ${text}` ).toBe( false );
			}
		});

		const response = await page.waitForResponse( response => response.url().includes( 'formbricks.umd.cjs' ) );
		expect( response.ok() ).toBe( true );

		const consoleMessage = await page.waitForEvent( 'console', msg => msg.text().includes( '[DEBUG] - Start setup' ) );
		expect( consoleMessage ).toBeTruthy();
	});
});
