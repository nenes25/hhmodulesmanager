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

/*
 * Minimal stubs of PrestaShop classes for unit testing in CI environment
 * These are only loaded when PrestaShop is not available (e.g., in GitHub Actions)
 */

if (!class_exists('Configuration')) {
    /**
     * Stub for PrestaShop Configuration class
     */
    class Configuration
    {
        public static function updateGlobalValue($key, $value)
        {
            return true;
        }

        public static function get($key)
        {
            return null;
        }

        public static function deleteByName($key)
        {
            return true;
        }
    }
}

if (!class_exists('Module')) {
    /**
     * Stub for PrestaShop Module class
     */
    class Module
    {
        public $name;
        public $version;
        public $author;

        public static function getInstanceByName($name)
        {
            return null;
        }

        public function get($serviceName)
        {
            return false;
        }
    }
}

if (!class_exists('Tools')) {
    /**
     * Stub for PrestaShop Tools class
     */
    class Tools
    {
        public static function getValue($key, $default = null)
        {
            return $default;
        }
    }
}

if (!class_exists('Context')) {
    /**
     * Stub for PrestaShop Context class
     */
    class Context
    {
        public static function getContext()
        {
            static $context = null;
            if ($context === null) {
                $context = new self();
            }

            return $context;
        }
    }
}

if (!class_exists('Shop')) {
    /**
     * Stub for PrestaShop Shop class
     */
    class Shop
    {
        public const CONTEXT_SHOP = 1;
        public const CONTEXT_GROUP = 2;
        public const CONTEXT_ALL = 4;

        public static function getContext()
        {
            return self::CONTEXT_SHOP;
        }
    }
}

if (!class_exists('Db')) {
    /**
     * Stub for PrestaShop Db class
     */
    class Db
    {
        public static function getInstance()
        {
            static $instance = null;
            if ($instance === null) {
                $instance = new self();
            }

            return $instance;
        }

        public function execute($sql)
        {
            return true;
        }

        public function getValue($sql)
        {
            return null;
        }

        public function getRow($sql)
        {
            return [];
        }

        public function executeS($sql)
        {
            return [];
        }
    }
}

if (!class_exists('Language')) {
    /**
     * Stub for PrestaShop Language class
     */
    class Language
    {
        public $id = 1;
        public $iso_code = 'en';
    }
}

if (!class_exists('PrestaShopException')) {
    /**
     * Stub for PrestaShop PrestaShopException class
     */
    class PrestaShopException extends Exception
    {
    }
}

if (!class_exists('ObjectModel')) {
    /**
     * Stub for PrestaShop ObjectModel class
     */
    class ObjectModel
    {
        // Type constants
        public const TYPE_INT = 1;
        public const TYPE_BOOL = 2;
        public const TYPE_STRING = 3;
        public const TYPE_FLOAT = 4;
        public const TYPE_DATE = 5;
        public const TYPE_HTML = 6;
        public const TYPE_NOTHING = 7;
        public const TYPE_SQL = 8;

        public $id;
        public $id_lang;

        public function __construct($id = null)
        {
            $this->id = $id;
        }

        public function add($autodate = true, $null_values = false)
        {
            return true;
        }

        public function update($null_values = false)
        {
            return true;
        }

        public function delete()
        {
            return true;
        }

        public function save($null_values = false, $autodate = true)
        {
            if ($this->id) {
                return $this->update($null_values);
            }

            return $this->add($autodate, $null_values);
        }

        public static function getDefinition($class)
        {
            return [
                'table' => 'test',
                'primary' => 'id',
                'fields' => [],
            ];
        }
    }
}
