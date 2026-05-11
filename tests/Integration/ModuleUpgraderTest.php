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

use Hhennes\ModulesManager\Upgrader\Module;
use PHPUnit\Framework\TestCase;
use PrestaShop\PrestaShop\Core\Addon\Module\ModuleManagerBuilder;

/**
 * Integration tests for Module Upgrader
 *
 * These tests require a working PrestaShop installation with module management capability.
 * We test with ps_banner which is a standard PrestaShop module.
 */
class ModuleUpgraderTest extends TestCase
{
    /** @var Module */
    private $upgrader;

    /** @var \PrestaShop\PrestaShop\Core\Addon\Module\ModuleManager */
    private $moduleManager;

    /** @var string Test module name (using a standard PS module) */
    private const TEST_MODULE = 'ps_emailsubscription';

    protected function setUp(): void
    {
        $this->upgrader = new Module();

        $moduleManagerBuilder = ModuleManagerBuilder::getInstance();
        $this->moduleManager = $moduleManagerBuilder->build();
    }

    public function testUpgradeWithNoModuleData(): void
    {
        $data = [
            'configuration' => [],
            'translation' => [],
        ];

        $this->upgrader->upgrade($data);

        $this->assertEmpty($this->upgrader->getErrors());
        $this->assertEmpty($this->upgrader->getSuccess());
    }

    public function testUpgradeWithEmptyModuleSection(): void
    {
        $data = [
            'modules' => [],
        ];

        $this->upgrader->upgrade($data);

        $this->assertEmpty($this->upgrader->getErrors());
        $this->assertEmpty($this->upgrader->getSuccess());
    }

    public function testUpgradeInstallModule(): void
    {
        // Ensure module is uninstalled first
        if ($this->moduleManager->isInstalled(self::TEST_MODULE)) {
            $this->moduleManager->uninstall(self::TEST_MODULE);
        }

        $data = [
            'modules' => [
                'install' => [self::TEST_MODULE],
            ],
        ];

        $this->upgrader->upgrade($data);

        $success = $this->upgrader->getSuccess();
        $this->assertNotEmpty($success);
        $this->assertStringContainsString(self::TEST_MODULE, $success[0]);
        $this->assertStringContainsString('installed', $success[0]);
        $this->assertEmpty($this->upgrader->getErrors());

        // Verify module is now installed
        $this->assertTrue($this->moduleManager->isInstalled(self::TEST_MODULE));
    }

    public function testUpgradeErrorsWhenInstallingAlreadyInstalledModule(): void
    {
        // Ensure module is installed first
        if (!$this->moduleManager->isInstalled(self::TEST_MODULE)) {
            $this->moduleManager->install(self::TEST_MODULE);
        }

        $data = [
            'modules' => [
                'install' => [self::TEST_MODULE],
            ],
        ];

        $this->upgrader->upgrade($data);

        $errors = $this->upgrader->getErrors();
        $this->assertNotEmpty($errors);
        $this->assertStringContainsString(self::TEST_MODULE, $errors[0]);
        $this->assertStringContainsString('already installed', $errors[0]);
        $this->assertEmpty($this->upgrader->getSuccess());
    }

    public function testUpgradeEnableModule(): void
    {
        // Ensure module is installed and disabled
        if (!$this->moduleManager->isInstalled(self::TEST_MODULE)) {
            $this->moduleManager->install(self::TEST_MODULE);
        }
        if ($this->moduleManager->isEnabled(self::TEST_MODULE)) {
            $this->moduleManager->disable(self::TEST_MODULE);
        }

        $data = [
            'modules' => [
                'enable' => [self::TEST_MODULE],
            ],
        ];

        $this->upgrader->upgrade($data);

        $success = $this->upgrader->getSuccess();
        $this->assertNotEmpty($success);
        $this->assertStringContainsString(self::TEST_MODULE, $success[0]);
        $this->assertStringContainsString('enabled', $success[0]);
        $this->assertEmpty($this->upgrader->getErrors());

        // Verify module is now enabled
        $this->assertTrue($this->moduleManager->isEnabled(self::TEST_MODULE));
    }

    public function testUpgradeErrorsWhenEnablingNotInstalledModule(): void
    {
        // Use a module that doesn't exist
        $nonExistentModule = 'nonexistentmodule_test_' . time();

        $data = [
            'modules' => [
                'enable' => [$nonExistentModule],
            ],
        ];

        $this->upgrader->upgrade($data);

        $errors = $this->upgrader->getErrors();
        $this->assertNotEmpty($errors);
        $this->assertStringContainsString($nonExistentModule, $errors[0]);
        $this->assertStringContainsString('not installed', $errors[0]);
        $this->assertEmpty($this->upgrader->getSuccess());
    }

    public function testUpgradeDisableModule(): void
    {
        // Ensure module is installed and enabled
        if (!$this->moduleManager->isInstalled(self::TEST_MODULE)) {
            $this->moduleManager->install(self::TEST_MODULE);
        }
        if (!$this->moduleManager->isEnabled(self::TEST_MODULE)) {
            $this->moduleManager->enable(self::TEST_MODULE);
        }

        $data = [
            'modules' => [
                'disable' => [self::TEST_MODULE],
            ],
        ];

        $this->upgrader->upgrade($data);

        $success = $this->upgrader->getSuccess();
        $this->assertNotEmpty($success);
        $this->assertStringContainsString(self::TEST_MODULE, $success[0]);
        $this->assertStringContainsString('disabled', $success[0]);
        $this->assertEmpty($this->upgrader->getErrors());

        // Verify module is now disabled
        $this->assertFalse($this->moduleManager->isEnabled(self::TEST_MODULE));
    }

    public function testUpgradeUninstallModule(): void
    {
        // Ensure module is installed first
        if (!$this->moduleManager->isInstalled(self::TEST_MODULE)) {
            $this->moduleManager->install(self::TEST_MODULE);
        }

        $data = [
            'modules' => [
                'uninstall' => [self::TEST_MODULE],
            ],
        ];

        $this->upgrader->upgrade($data);

        $success = $this->upgrader->getSuccess();
        $this->assertNotEmpty($success);
        $this->assertStringContainsString(self::TEST_MODULE, $success[0]);
        $this->assertStringContainsString('uninstalled', $success[0]);
        $this->assertEmpty($this->upgrader->getErrors());

        // Verify module is now uninstalled
        $this->assertFalse($this->moduleManager->isInstalled(self::TEST_MODULE));
    }

    public function testUpgradeHandlesMultipleActions(): void
    {
        // Setup: uninstall if installed
        if ($this->moduleManager->isInstalled(self::TEST_MODULE)) {
            $this->moduleManager->uninstall(self::TEST_MODULE);
        }

        $data = [
            'modules' => [
                'install' => [self::TEST_MODULE],
                'enable' => [self::TEST_MODULE],
            ],
        ];

        $this->upgrader->upgrade($data);

        $success = $this->upgrader->getSuccess();
        // We should get success for install, but error for enable (already enabled after install)
        $this->assertNotEmpty($success);
        $this->assertTrue($this->moduleManager->isInstalled(self::TEST_MODULE));
        $this->assertTrue($this->moduleManager->isEnabled(self::TEST_MODULE));

        // Cleanup
        $this->moduleManager->uninstall(self::TEST_MODULE);
    }

    public function testResetResults(): void
    {
        // Ensure module is uninstalled
        if ($this->moduleManager->isInstalled(self::TEST_MODULE)) {
            $this->moduleManager->uninstall(self::TEST_MODULE);
        }

        $data = [
            'modules' => [
                'install' => [self::TEST_MODULE],
            ],
        ];

        $this->upgrader->upgrade($data);
        $this->assertNotEmpty($this->upgrader->getSuccess());

        // Reset results
        $this->upgrader->resetResults();

        $this->assertEmpty($this->upgrader->getSuccess());
        $this->assertEmpty($this->upgrader->getErrors());

        // Cleanup
        $this->moduleManager->uninstall(self::TEST_MODULE);
    }

    protected function tearDown(): void
    {
        // Cleanup: ensure test module is in a known state (installed and enabled)
        if (!$this->moduleManager->isInstalled(self::TEST_MODULE)) {
            $this->moduleManager->install(self::TEST_MODULE);
        }
        if (!$this->moduleManager->isEnabled(self::TEST_MODULE)) {
            $this->moduleManager->enable(self::TEST_MODULE);
        }
    }
}
