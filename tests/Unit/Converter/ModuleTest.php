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

namespace Hhennes\ModulesManager\Tests\Unit\Converter;

use Hhennes\ModulesManager\Change;
use Hhennes\ModulesManager\Converter\Module;
use PHPUnit\Framework\TestCase;

class ModuleTest extends TestCase
{
    /** @var Module */
    private $converter;

    protected function setUp(): void
    {
        $this->converter = new Module();
    }

    public function testCanConvertReturnsTrueForModuleEntity(): void
    {
        $change = $this->createMock(Change::class);
        $change->entity = 'module';

        $result = $this->converter->canConvert($change);

        $this->assertTrue($result);
    }

    public function testCanConvertReturnsFalseForNonModuleEntity(): void
    {
        $change = $this->createMock(Change::class);
        $change->entity = 'configuration';

        $result = $this->converter->canConvert($change);

        $this->assertFalse($result);
    }

    public function testConvertAddsModuleInstallToArray(): void
    {
        $change = $this->createMock(Change::class);
        $change->entity = 'module';
        $change->action = 'install';
        $change->key = 'ps_checkout';

        $currentChanges = [];
        $this->converter->convert($change, $currentChanges);

        $this->assertArrayHasKey('modules', $currentChanges);
        $this->assertArrayHasKey('install', $currentChanges['modules']);
        $this->assertContains('ps_checkout', $currentChanges['modules']['install']);
    }

    public function testConvertAddsModuleUninstallToArray(): void
    {
        $change = $this->createMock(Change::class);
        $change->entity = 'module';
        $change->action = 'uninstall';
        $change->key = 'ps_oldmodule';

        $currentChanges = [];
        $this->converter->convert($change, $currentChanges);

        $this->assertArrayHasKey('modules', $currentChanges);
        $this->assertArrayHasKey('uninstall', $currentChanges['modules']);
        $this->assertContains('ps_oldmodule', $currentChanges['modules']['uninstall']);
    }

    public function testConvertAddsModuleEnableToArray(): void
    {
        $change = $this->createMock(Change::class);
        $change->entity = 'module';
        $change->action = 'enable';
        $change->key = 'ps_facetedsearch';

        $currentChanges = [];
        $this->converter->convert($change, $currentChanges);

        $this->assertArrayHasKey('modules', $currentChanges);
        $this->assertArrayHasKey('enable', $currentChanges['modules']);
        $this->assertContains('ps_facetedsearch', $currentChanges['modules']['enable']);
    }

    public function testConvertAddsModuleDisableToArray(): void
    {
        $change = $this->createMock(Change::class);
        $change->entity = 'module';
        $change->action = 'disable';
        $change->key = 'ps_banner';

        $currentChanges = [];
        $this->converter->convert($change, $currentChanges);

        $this->assertArrayHasKey('modules', $currentChanges);
        $this->assertArrayHasKey('disable', $currentChanges['modules']);
        $this->assertContains('ps_banner', $currentChanges['modules']['disable']);
    }

    public function testConvertAddsModuleUpdateToArray(): void
    {
        $change = $this->createMock(Change::class);
        $change->entity = 'module';
        $change->action = 'update';
        $change->key = 'ps_emailsubscription';

        $currentChanges = [];
        $this->converter->convert($change, $currentChanges);

        $this->assertArrayHasKey('modules', $currentChanges);
        $this->assertArrayHasKey('update', $currentChanges['modules']);
        $this->assertContains('ps_emailsubscription', $currentChanges['modules']['update']);
    }

    public function testConvertThrowsExceptionForInvalidAction(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Unknow configuration action');

        $change = $this->createMock(Change::class);
        $change->entity = 'module';
        $change->action = 'invalid_action';
        $change->key = 'ps_test';

        $currentChanges = [];
        $this->converter->convert($change, $currentChanges);
    }

    public function testConvertMergesMultipleInstalls(): void
    {
        // First module install
        $change1 = $this->createMock(Change::class);
        $change1->entity = 'module';
        $change1->action = 'install';
        $change1->key = 'ps_checkout';

        // Second module install
        $change2 = $this->createMock(Change::class);
        $change2->entity = 'module';
        $change2->action = 'install';
        $change2->key = 'ps_facetedsearch';

        $currentChanges = [];
        $this->converter->convert($change1, $currentChanges);
        $this->converter->convert($change2, $currentChanges);

        $this->assertCount(2, $currentChanges['modules']['install']);
        $this->assertContains('ps_checkout', $currentChanges['modules']['install']);
        $this->assertContains('ps_facetedsearch', $currentChanges['modules']['install']);
    }

    public function testConvertMergesMixedActions(): void
    {
        // Install
        $change1 = $this->createMock(Change::class);
        $change1->entity = 'module';
        $change1->action = 'install';
        $change1->key = 'ps_checkout';

        // Uninstall
        $change2 = $this->createMock(Change::class);
        $change2->entity = 'module';
        $change2->action = 'uninstall';
        $change2->key = 'ps_oldmodule';

        // Enable
        $change3 = $this->createMock(Change::class);
        $change3->entity = 'module';
        $change3->action = 'enable';
        $change3->key = 'ps_banner';

        $currentChanges = [];
        $this->converter->convert($change1, $currentChanges);
        $this->converter->convert($change2, $currentChanges);
        $this->converter->convert($change3, $currentChanges);

        $this->assertArrayHasKey('install', $currentChanges['modules']);
        $this->assertArrayHasKey('uninstall', $currentChanges['modules']);
        $this->assertArrayHasKey('enable', $currentChanges['modules']);
        $this->assertCount(1, $currentChanges['modules']['install']);
        $this->assertCount(1, $currentChanges['modules']['uninstall']);
        $this->assertCount(1, $currentChanges['modules']['enable']);
    }

    public function testConvertPreventsDuplicateModulesInSameAction(): void
    {
        // First install
        $change1 = $this->createMock(Change::class);
        $change1->entity = 'module';
        $change1->action = 'install';
        $change1->key = 'ps_checkout';

        // Second install of same module (should not create duplicate)
        $change2 = $this->createMock(Change::class);
        $change2->entity = 'module';
        $change2->action = 'install';
        $change2->key = 'ps_checkout';

        $currentChanges = [];
        $this->converter->convert($change1, $currentChanges);
        $this->converter->convert($change2, $currentChanges);

        $this->assertCount(1, $currentChanges['modules']['install']);
        $this->assertContains('ps_checkout', $currentChanges['modules']['install']);
    }

    public function testAllAllowedActionsAreSupported(): void
    {
        $allowedActions = ['install', 'uninstall', 'enable', 'disable', 'update'];

        foreach ($allowedActions as $action) {
            $change = $this->createMock(Change::class);
            $change->entity = 'module';
            $change->action = $action;
            $change->key = 'ps_testmodule';

            $currentChanges = [];

            // Should not throw exception
            $this->converter->convert($change, $currentChanges);

            $this->assertArrayHasKey($action, $currentChanges['modules']);
            $this->assertContains('ps_testmodule', $currentChanges['modules'][$action]);
        }
    }
}
