# Themeisle SDK

ThemeIsle SDK used to register common features for products in the portfolio.

Can be installed using composer:
`composer require codeinwp/themeisle-sdk`
and manually autoloading the load.php file in the composer.json file of your project:

```

  "autoload": {
    "files": [
      "vendor/codeinwp/themeisle-sdk/load.php"
    ]
  }

```

### Features

- Loads the most recent version of the library across all the products on the same wordpress instance. For instance if there is a theme which bundles v2.0.0 of the SDK and one plugin which bundles the v1.9.1, it will load on the most recent one, v2.0.0 for both products.
- If there are two products using the same version, it will load the first one that register the SDK, unless it's explicitly overwritten.
- Each functionality is bundled into modules, which are loaded based on the product type. Free/Pro, is available on wordpress or not.
- Telemetry. Track the use of the feature. [Check the docs to learn more](./docs/telemetry.md).

### How to register product

- The library works out of the box by simply loading the autoloader into the plugin/theme files.
- Some modules are loaded only if the product is not available on WordPress.org ( licenser/review ). You can define if the product is available on wordpress.org by adding this file header `WordPress Available:  <yes|no>` where `<yes|no>` will be replaced with the proper status.
- If the product requires is a premium one and requires a licesing mechanism, we can use `Requires License: <yes|no>` to specifically tell that the product requires license.

### Guides

**Setup & Testing**
- [Add and use Telemetry in a product](./docs/TELEMETRY.md)
- [Running and adding an E2E test](./docs/E2E.md)

**Feature Modules**
- [Licenser — license activation, updates, WP-CLI](./docs/LICENSER.md)
- [Logger — anonymous usage tracking](./docs/LOGGER.md)
- [Notifications — admin notice queue](./docs/NOTIFICATIONS.md)
- [Review — WordPress.org review prompts](./docs/REVIEW.md)
- [Promotions — cross-product promotional notices](./docs/PROMOTIONS.md)
- [Rollback — revert to a previous version](./docs/ROLLBACK.md)
- [Uninstall Feedback — deactivation survey](./docs/UNINSTALL-FEEDBACK.md)
- [About Us — company/product about page](./docs/ABOUT-US.md)
- [Float Widget — floating help panel](./docs/FLOAT-WIDGET.md)
- [Announcements — Black Friday and sale banners](./docs/ANNOUNCEMENTS.md)
- [Welcome — post-install upgrade offer](./docs/WELCOME.md)
- [Compatibilities — version compatibility warnings](./docs/COMPATIBILITIES.md)

**AI Agent Reference**
- [AGENTS.md](./AGENTS.md) — quick-reference for AI agents working in this codebase


