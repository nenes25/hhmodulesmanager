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

namespace Hhennes\ModulesManager\Tests\Integration;

use Hhennes\ModulesManager\Upgrader\Configuration;
use PHPUnit\Framework\TestCase;

/**
 * Integration tests for Configuration Upgrader
 *
 * These tests require a working PrestaShop installation with database access.
 * They test the actual behavior of applying configuration changes.
 */
class ConfigurationUpgraderTest extends TestCase
{
    /** @var Configuration */
    private $upgrader;

    protected function setUp(): void
    {
        $this->upgrader = new Configuration();
    }

    public function testUpgradeWithNoConfigurationData(): void
    {
        $data = [
            'modules' => [],
            'translation' => [],
        ];

        $this->upgrader->upgrade($data);

        $this->assertEmpty($this->upgrader->getErrors());
        $this->assertEmpty($this->upgrader->getSuccess());
    }

    public function testUpgradeWithEmptyConfigurationSection(): void
    {
        $data = [
            'configuration' => [],
        ];

        $this->upgrader->upgrade($data);

        $this->assertEmpty($this->upgrader->getErrors());
        $this->assertEmpty($this->upgrader->getSuccess());
    }

    public function testUpgradeAddsOrUpdatesConfiguration(): void
    {
        $testKey = 'HHMODULESMANAGER_TEST_CONFIG_' . time();
        $testValue = 'test_value_' . time();

        $data = [
            'configuration' => [
                'add_or_update' => [
                    $testKey => $testValue,
                ],
            ],
        ];

        $this->upgrader->upgrade($data);

        $success = $this->upgrader->getSuccess();
        $this->assertNotEmpty($success);
        $this->assertStringContainsString($testKey, $success[0]);
        $this->assertStringContainsString('added or updated', $success[0]);
        $this->assertEmpty($this->upgrader->getErrors());

        // Verify the configuration was actually set
        $this->assertEquals($testValue, \Configuration::get($testKey));

        // Cleanup
        \Configuration::deleteByName($testKey);
    }

    public function testUpgradeHandlesArrayValues(): void
    {
        $testKey = 'HHMODULESMANAGER_TEST_ARRAY_' . time();
        $testValue = ['key1' => 'value1', 'key2' => 'value2'];

        $data = [
            'configuration' => [
                'add_or_update' => [
                    $testKey => $testValue,
                ],
            ],
        ];

        $this->upgrader->upgrade($data);

        $success = $this->upgrader->getSuccess();
        $this->assertNotEmpty($success);
        $this->assertEmpty($this->upgrader->getErrors());

        // Verify the array was JSON encoded
        $storedValue = \Configuration::get($testKey);
        $this->assertIsString($storedValue);
        $this->assertEquals($testValue, json_decode($storedValue, true));

        // Cleanup
        \Configuration::deleteByName($testKey);
    }

    public function testUpgradeDeletesConfiguration(): void
    {
        $testKey = 'HHMODULESMANAGER_TEST_DELETE_' . time();

        // First, create the configuration
        \Configuration::updateGlobalValue($testKey, 'test_value');
        $this->assertNotEmpty(\Configuration::get($testKey));

        // Now delete it via upgrader
        $data = [
            'configuration' => [
                'delete' => [$testKey],
            ],
        ];

        $this->upgrader->upgrade($data);

        $success = $this->upgrader->getSuccess();
        $this->assertNotEmpty($success);
        $this->assertStringContainsString($testKey, $success[0]);
        $this->assertStringContainsString('deleted', $success[0]);
        $this->assertEmpty($this->upgrader->getErrors());

        // Verify it was deleted
        $this->assertEmpty(\Configuration::get($testKey));
    }

    public function testUpgradeErrorsWhenDeletingNonExistentConfiguration(): void
    {
        $testKey = 'HHMODULESMANAGER_NONEXISTENT_' . time();

        $data = [
            'configuration' => [
                'delete' => [$testKey],
            ],
        ];

        $this->upgrader->upgrade($data);

        $errors = $this->upgrader->getErrors();
        $this->assertNotEmpty($errors);
        $this->assertStringContainsString($testKey, $errors[0]);
        $this->assertStringContainsString('not exists', $errors[0]);
        $this->assertEmpty($this->upgrader->getSuccess());
    }

    public function testUpgradeHandlesMultipleAddOrUpdates(): void
    {
        $testKey1 = 'HHMODULESMANAGER_TEST_MULTI1_' . time();
        $testKey2 = 'HHMODULESMANAGER_TEST_MULTI2_' . time();

        $data = [
            'configuration' => [
                'add_or_update' => [
                    $testKey1 => 'value1',
                    $testKey2 => 'value2',
                ],
            ],
        ];

        $this->upgrader->upgrade($data);

        $success = $this->upgrader->getSuccess();
        $this->assertCount(2, $success);
        $this->assertEmpty($this->upgrader->getErrors());

        // Verify both configurations were set
        $this->assertEquals('value1', \Configuration::get($testKey1));
        $this->assertEquals('value2', \Configuration::get($testKey2));

        // Cleanup
        \Configuration::deleteByName($testKey1);
        \Configuration::deleteByName($testKey2);
    }

    public function testUpgradeHandlesBothAddAndDelete(): void
    {
        $addKey = 'HHMODULESMANAGER_TEST_ADD_' . time();
        $deleteKey = 'HHMODULESMANAGER_TEST_DEL_' . time();

        // Create the key to be deleted
        \Configuration::updateGlobalValue($deleteKey, 'to_delete');

        $data = [
            'configuration' => [
                'add_or_update' => [
                    $addKey => 'new_value',
                ],
                'delete' => [$deleteKey],
            ],
        ];

        $this->upgrader->upgrade($data);

        $success = $this->upgrader->getSuccess();
        $this->assertCount(2, $success);
        $this->assertEmpty($this->upgrader->getErrors());

        // Verify add
        $this->assertEquals('new_value', \Configuration::get($addKey));

        // Verify delete
        $this->assertEmpty(\Configuration::get($deleteKey));

        // Cleanup
        \Configuration::deleteByName($addKey);
    }

    public function testResetResults(): void
    {
        $testKey = 'HHMODULESMANAGER_TEST_RESET_' . time();

        $data = [
            'configuration' => [
                'add_or_update' => [
                    $testKey => 'value',
                ],
            ],
        ];

        $this->upgrader->upgrade($data);
        $this->assertNotEmpty($this->upgrader->getSuccess());

        // Reset results
        $this->upgrader->resetResults();

        $this->assertEmpty($this->upgrader->getSuccess());
        $this->assertEmpty($this->upgrader->getErrors());

        // Cleanup
        \Configuration::deleteByName($testKey);
    }

    public function testUpgradeHandlesBooleanValues(): void
    {
        $testKey = 'HHMODULESMANAGER_TEST_BOOL_' . time();

        $data = [
            'configuration' => [
                'add_or_update' => [
                    $testKey => true,
                ],
            ],
        ];

        $this->upgrader->upgrade($data);

        $this->assertNotEmpty($this->upgrader->getSuccess());
        $this->assertEquals('1', \Configuration::get($testKey));

        // Cleanup
        \Configuration::deleteByName($testKey);
    }

    public function testUpgradeHandlesNumericValues(): void
    {
        $testKey = 'HHMODULESMANAGER_TEST_NUM_' . time();

        $data = [
            'configuration' => [
                'add_or_update' => [
                    $testKey => 42,
                ],
            ],
        ];

        $this->upgrader->upgrade($data);

        $this->assertNotEmpty($this->upgrader->getSuccess());
        $this->assertEquals('42', \Configuration::get($testKey));

        // Cleanup
        \Configuration::deleteByName($testKey);
    }
}
