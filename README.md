<a href="https://newfold.com/" target="_blank">
    <img src="https://newfold.com/content/experience-fragments/newfold/site-header/master/_jcr_content/root/header/logo.coreimg.svg/1621395071423/newfold-digital.svg" alt="Newfold Logo" title="Newfold Digital" align="right" 
height="42" />
</a>

# WordPress Installer Module
[![Version Number](https://img.shields.io/github/v/release/newfold-labs/wp-module-installer?color=21a0ed&labelColor=333333)](https://github.com/newfold/wp-module-installer/releases)
[![License](https://img.shields.io/github/license/newfold-labs/wp-module-installer?labelColor=333333&color=666666)](https://raw.githubusercontent.com/newfold-labs/wp-module-installer/master/LICENSE)

An installer for WordPress plugins and themes.

## Module Responsibilities

- Store a list of valid WordPress, Newfold, and custom URL plugins and themes, hereinafter referred to as valid plugins and themes.
- Provide REST API endpoints to install and/or activate valid plugins and themes synchronously or asynchronously (using WP_Cron).
- Provide REST API endpoints to check the status of asynchronous requests and functionality to expedite them.
- Provide Service classes that contain all the functionality which can be used independently of the REST API.
- Provide flexibility to install and activate valid plugins and themes synchronously or asynchronously (using WP_Cron).


## Critical Paths

- If a user requests a valid plugin or theme installation/activation, it should be completed successfully.
- If a user requests an invalid/non-approved plugin or theme installation/activation, it should fail.
- If a user requests to asynchronously install/activate an approved plugin or theme, then the API returns the correct status of the request in the queue.
- If a user requests to expedite an asynchronously queued valid plugin or theme installation/activation, then it must be completed successfully without affecting other requests.

## Releases

### 1. Bump Version [IMPORTANT]

- Update the module version in the `includes/Data/Constants.php` file (the NFD_INSTALLER_VERSION const).
- Update the module version in the `package.json and package-lock.json` file as well.

## Installation

### 1. Add the Newfold Satis to your `composer.json`.

 ```bash
 composer config repositories.newfold composer https://newfold-labs.github.io/satis
 ```

### 2. Require the `newfold-labs/wp-module-installer` package.

 ```bash
 composer require newfold-labs/wp-module-installer
 ```

[More on Newfold WordPress Modules](https://github.com/newfold-labs/wp-module-loader)
