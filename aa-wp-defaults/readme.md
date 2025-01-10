# alpha alpaka WordPress Defaults

This repository contains a set of default functions and settings for WordPress, specifically tailored by alpha alpaka. 

It includes enhancements and maintenance modes to streamline WordPress performance, user experience and developer experience.

## Features

The features are grouped into the following categories / files:

- acfSync.php: Synchronizes ACF fields between environments.
- cacheSettings.php: Configures caching settings for WordPress (especially for Rocket Cache Plugin).
- cronJobs.php: adds several cronjob scheduling functions.
- dashboard.php: removes default dashboard widgets
- functions.php: several functions that are needed in multiple projects
- helpers.php: helper functions
- removeWPStandardThings.php: Disables various default WordPress features to enhance performance.
- removeAdminAlerts.php: Removes admin notices and messages from the admin dashboard.
- maintenanceMode.php: Manages maintenance modes for both frontend and backend, with settings managed through the admin interface.
- settins.php: adds some standard settings to the installation

## Installation

1. Clone or download this repo into the mu-plugins folder
2. Activate the plugin through the WordPress admin interface under the Plugins section.

OR

1. Use it as git-submodule in your project

## Requirements

- PHP 7.4 or higher
- WordPress 5.0 or higher