# AI Connect

Offers the Easy MCP connector to products that expose WordPress abilities: a
plugin-row link, a dismissable notice and one modal that enables the connector
and opens the user's AI agent with the site pre-filled.

## Opting in

```php
add_filter( '<product_key>_ai_connect_metadata', function () {
	return [
		'name'         => 'Optimole', // optional, defaults to the product's friendly name
		'notice_cases' => [ 'optimize new uploads', 'purge cached images', 'offload originals to the cloud' ], // up to 3
		'prompts'      => [ 'Show me my Optimole delivery settings and explain what each one does.' ], // up to 5
		'abilities'    => [ 'optimole/get-delivery-settings' ], // optional
		'internal_slug' => 'optimole-wp', // optional, only when the product passes another slug to themeisle_internal_page
	];
} );
```

A product that returns no `notice_cases` or no `prompts` is treated as not opted
in. `abilities` are the ability names switched on in Easy MCP when the user
presses Enable; names that are not registered are ignored, and nothing the site
owner enabled before is removed. Leave out abilities that move money, publish
outward, run code or change security policy.

## Behaviour

| | |
| --- | --- |
| Who | Users who can `install_plugins` and `activate_plugins`. |
| Where | The plugins list, and the product's own pages (`do_action( 'themeisle_internal_page', $slug, $page )`). On the product's pages the other notices are hidden while it shows. |
| When | The notice waits one day after the product's install time. The plugin-row link shows at once. |
| One per screen | However many products opted in, one notice and one modal are printed. On the plugins list the product installed first speaks; on an internal page, its owner. |
| Dismissal | Per user (`themeisle_sdk_ai_connect_dismissed` user meta), not per site. |
| Easy MCP active | The module does not load: no notice, no row link, no modal. |
| Enable | AJAX, nonce + capability checked: installs `easy-mcp-ai` from WordPress.org when missing, activates it, switches the product's abilities on, returns the MCP URL and the deep links. |

Themes get the notice only; there is no plugin row for a theme.

All strings are in `Loader::$labels['ai_connect']`.
