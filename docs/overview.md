---
name: wp-module-installer
title: Overview
description: What the module does and who maintains it.
updated: 2025-03-18
---

# Overview

## What the module does

**wp-module-installer** provides installation and activation of WordPress plugins and themes from within the brand plugin. It registers with the Newfold Module Loader as the `installer` module (hidden) and provides:

- **REST API** – Endpoints for installing/activating plugins and themes (used by the admin app).
- **Task manager** – Background or queued install tasks.
- **WP-CLI** – Commands for install/activate operations.
- **Admin UI** – When the request is an authorized admin REST context, loads WPAdmin for admin-facing flows.

The module depends on **wp-module-pls** for license provisioning and validation where required.

## Who maintains it

- **Newfold Labs** (Newfold Digital). Distributed via Newfold Satis.
