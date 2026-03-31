# Hryvinskyi_BotBlocker Module

## Overview

The Hryvinskyi_BotBlocker module is designed to enhance your Magento 2 store's security by tracking and blocking IP addresses that exceed a specified request limit within a defined timeframe. This module allows you to configure the blocking threshold, timeframe, and a whitelist of IP addresses.

## Installation

To install the Hryvinskyi_BotBlocker module, follow these steps:

1. Copy the module files to your Magento 2 installation.
2. Run the following commands in your Magento 2 root directory:
    ```shell
    php bin/magento module:enable Hryvinskyi_BotBlocker
    php bin/magento setup:upgrade
    php bin/magento cache:clean
    ```

## Configuration

You can configure the behavior of the Hryvinskyi_BotBlocker module through the Magento Admin Panel under `Stores > Configuration -> Hryvinskyi Extensions > Bot Blocker.`. The module allows you to customize the following settings:

- **Threshold:** Set the maximum number of requests an IP address can make within a specified timeframe before it is blocked.
- **Timeframe:** Define the time window during which the threshold count is calculated.
- **Whitelist:** Specify a list of IP addresses that should be exempt from the blocking rules.
- **Storage Method:** Choose where to store the tracking data, either in Redis or in MySQL.

## Usage

The Hryvinskyi_BotBlocker module tracks and blocks IP addresses based on the configuration you set. Here's how it works:

1. When an IP address makes a request to your website, the module tracks the request count and the time of the first request.
2. If the request count exceeds the configured threshold within the specified timeframe, the IP address is blocked.
3. IP addresses on the whitelist are exempt from being blocked.

## Extensibility

As a developer, you can extend and customize the behavior of the Hryvinskyi_BotBlocker module. You may override the functionality of the module by interacting with its codebase. For more information, refer to the [Magento extension mechanism](https://devdocs.magento.com/guides/v2.4/extension-dev-guide/plugins.html).