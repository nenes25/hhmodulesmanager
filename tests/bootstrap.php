<?php

/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file docs/licenses/LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to contact@h-hennes.fr so we can send you a copy immediately.
 *
 * @author    Hervé HENNES <contact@h-hhennes.fr>
 * @copyright since 2023 Hervé HENNES
 * @license   https://opensource.org/licenses/AFL-3.0  Academic Free License ("AFL") v. 3.0
 */

// Load composer autoloader
require_once __DIR__ . '/../vendor/autoload.php';

// PrestaShop constants are defined in phpunit.xml or phpunit.xml.dist
// They can be overridden in phpunit.xml for local configuration

// Load PrestaShop config if available and _PS_ROOT_DIR_ is properly configured (for integration tests)
if (defined('_PS_ROOT_DIR_') && _PS_ROOT_DIR_ !== '/path/to/prestashop') {
    $configFile = _PS_ROOT_DIR_ . '/config/config.inc.php';
    if (file_exists($configFile)) {
        require_once $configFile;
    }
}
