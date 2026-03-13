# Integration

## How the module registers

The module registers with the Newfold Module Loader on `plugins_loaded`:

- **name:** `installer`
- **label:** Installer (translatable)
- **callback:** Defines `NFD_INSTALLER_DIR`, creates `Constants( $container )` and `Installer( $container )`.
- **isActive:** true
- **isHidden:** true

**Constants** sets up any container or global constants the installer needs. **Installer** then instantiates:

- **RestApi()** – Registers REST routes for install/activate operations.
- **TaskManager()** – Manages install tasks (e.g. queue or background).
- **WPCLI()** – Registers WP-CLI commands.
- **WPAdmin()** – Loaded only when `Permissions::rest_is_authorized_admin()` is true (admin UI for installer flows).

## Permissions

`Permissions::rest_is_authorized_admin()` gates the admin UI. REST endpoints have their own permission callbacks. See the RestApi and Permissions classes for details.

## PLS dependency

The module uses **wp-module-pls** for license provisioning and validation when installing licensed plugins/themes. Ensure the PLS module is available in the host.
