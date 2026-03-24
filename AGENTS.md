# Agent guidance – wp-module-installer

This file gives AI agents a quick orientation to the repo. For full detail, see the **docs/** directory.

## What this project is

- **wp-module-installer** – Installer for WordPress plugins and themes. Registers with the loader as the `installer` module (hidden), provides REST API, WP-CLI, and admin UI for installing/activating plugins and themes. Depends on wp-module-pls for license/provisioning. Maintained by Newfold Labs.

- **Stack:** PHP 7.3+. Requires `newfold-labs/wp-module-pls`.

- **Architecture:** On `plugins_loaded` registers with the loader; callback defines `NFD_INSTALLER_DIR`, creates `Constants` and `Installer`. Installer wires RestApi, TaskManager, WPCLI, and (when authorized) WPAdmin.

## Key paths

| Purpose | Location |
|---------|----------|
| Bootstrap | `bootstrap.php` |
| Main module | `includes/Installer.php` – RestApi, TaskManager, WPCLI, WPAdmin |
| REST API | `includes/RestApi/` |
| Permissions | `includes/Permissions.php` |
| Tests | `tests/` |

## Essential commands

```bash
composer install
composer run lint
composer run fix
composer run test
composer run i18n
```

## Documentation

- **Full documentation** is in **docs/**. Start with **docs/index.md**.
- **CLAUDE.md** is a symlink to this file (AGENTS.md).

---

## Keeping documentation current

When you change code, features, or workflows, update the docs. Keep **docs/index.md** current: when you add, remove, or rename doc files, update the table of contents (and quick links if present). When adding or changing REST routes, update **api.md**. When cutting a release, update **docs/changelog.md**.
