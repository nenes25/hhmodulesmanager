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
use Hhennes\ModulesManager\Converter\Configuration;
use PHPUnit\Framework\TestCase;

class ConfigurationTest extends TestCase
{
    /** @var Configuration */
    private $converter;

    protected function setUp(): void
    {
        $this->converter = new Configuration();
    }

    public function testCanConvertReturnsTrueForConfigurationEntity(): void
    {
        $change = $this->createMock(Change::class);
        $change->entity = 'configuration';

        $result = $this->converter->canConvert($change);

        $this->assertTrue($result);
    }

    public function testCanConvertReturnsFalseForNonConfigurationEntity(): void
    {
        $change = $this->createMock(Change::class);
        $change->entity = 'translation';

        $result = $this->converter->canConvert($change);

        $this->assertFalse($result);
    }

    public function testConvertAddsConfigurationAddToArray(): void
    {
        $change = $this->createMock(Change::class);
        $change->entity = 'configuration';
        $change->action = 'add';
        $change->key = 'PS_SHOP_ENABLE';
        $change->details = json_encode([
            'configuration' => [
                'key' => 'PS_SHOP_ENABLE',
                'values' => '1',
                'idShop' => null,
                'idShopGroup' => null,
            ],
        ]);

        $currentChanges = [];
        $this->converter->convert($change, $currentChanges);

        $this->assertArrayHasKey('configuration', $currentChanges);
        $this->assertArrayHasKey('add_or_update', $currentChanges['configuration']);
        $this->assertArrayHasKey('PS_SHOP_ENABLE', $currentChanges['configuration']['add_or_update']);
        $this->assertEquals('1', $currentChanges['configuration']['add_or_update']['PS_SHOP_ENABLE']);
    }

    public function testConvertAddsConfigurationUpdateToArray(): void
    {
        $change = $this->createMock(Change::class);
        $change->entity = 'configuration';
        $change->action = 'update';
        $change->key = 'PS_SHOP_NAME';
        $change->details = json_encode([
            'configuration' => [
                'key' => 'PS_SHOP_NAME',
                'values' => 'My New Shop Name',
                'idShop' => 1,
                'idShopGroup' => null,
            ],
            'old_value' => 'Old Shop Name',
        ]);

        $currentChanges = [];
        $this->converter->convert($change, $currentChanges);

        $this->assertArrayHasKey('configuration', $currentChanges);
        $this->assertArrayHasKey('add_or_update', $currentChanges['configuration']);
        $this->assertArrayHasKey('PS_SHOP_NAME', $currentChanges['configuration']['add_or_update']);
        $this->assertEquals('My New Shop Name', $currentChanges['configuration']['add_or_update']['PS_SHOP_NAME']);
    }

    public function testConvertAddsConfigurationDeleteToArray(): void
    {
        $change = $this->createMock(Change::class);
        $change->entity = 'configuration';
        $change->action = 'delete';
        $change->key = 'PS_OLD_CONFIG';
        $change->details = json_encode([
            'configuration' => [
                'key' => 'PS_OLD_CONFIG',
                'values' => null,
            ],
            'name' => 'PS_OLD_CONFIG',
        ]);

        $currentChanges = [];
        $this->converter->convert($change, $currentChanges);

        $this->assertArrayHasKey('configuration', $currentChanges);
        $this->assertArrayHasKey('delete', $currentChanges['configuration']);
        $this->assertArrayHasKey('PS_OLD_CONFIG', $currentChanges['configuration']['delete']);
        $this->assertNull($currentChanges['configuration']['delete']['PS_OLD_CONFIG']);
    }

    public function testConvertThrowsExceptionForInvalidAction(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Unknow configuration action');

        $change = $this->createMock(Change::class);
        $change->entity = 'configuration';
        $change->action = 'invalid_action';
        $change->key = 'PS_TEST';
        $change->details = '{}';

        $currentChanges = [];
        $this->converter->convert($change, $currentChanges);
    }

    public function testConvertMergesMultipleAddOrUpdates(): void
    {
        // First change - add
        $change1 = $this->createMock(Change::class);
        $change1->entity = 'configuration';
        $change1->action = 'add';
        $change1->key = 'PS_CONFIG_1';
        $change1->details = json_encode([
            'configuration' => [
                'key' => 'PS_CONFIG_1',
                'values' => 'value1',
            ],
        ]);

        // Second change - update
        $change2 = $this->createMock(Change::class);
        $change2->entity = 'configuration';
        $change2->action = 'update';
        $change2->key = 'PS_CONFIG_2';
        $change2->details = json_encode([
            'configuration' => [
                'key' => 'PS_CONFIG_2',
                'values' => 'value2',
            ],
            'old_value' => 'old_value2',
        ]);

        $currentChanges = [];
        $this->converter->convert($change1, $currentChanges);
        $this->converter->convert($change2, $currentChanges);

        $this->assertCount(2, $currentChanges['configuration']['add_or_update']);
        $this->assertArrayHasKey('PS_CONFIG_1', $currentChanges['configuration']['add_or_update']);
        $this->assertArrayHasKey('PS_CONFIG_2', $currentChanges['configuration']['add_or_update']);
        $this->assertEquals('value1', $currentChanges['configuration']['add_or_update']['PS_CONFIG_1']);
        $this->assertEquals('value2', $currentChanges['configuration']['add_or_update']['PS_CONFIG_2']);
    }

    public function testConvertMergesMultipleDeletes(): void
    {
        // First delete
        $change1 = $this->createMock(Change::class);
        $change1->entity = 'configuration';
        $change1->action = 'delete';
        $change1->key = 'PS_DELETE_1';
        $change1->details = json_encode([
            'configuration' => [
                'key' => 'PS_DELETE_1',
                'values' => null,
            ],
            'name' => 'PS_DELETE_1',
        ]);

        // Second delete
        $change2 = $this->createMock(Change::class);
        $change2->entity = 'configuration';
        $change2->action = 'delete';
        $change2->key = 'PS_DELETE_2';
        $change2->details = json_encode([
            'configuration' => [
                'key' => 'PS_DELETE_2',
                'values' => null,
            ],
            'name' => 'PS_DELETE_2',
        ]);

        $currentChanges = [];
        $this->converter->convert($change1, $currentChanges);
        $this->converter->convert($change2, $currentChanges);

        $this->assertCount(2, $currentChanges['configuration']['delete']);
        $this->assertArrayHasKey('PS_DELETE_1', $currentChanges['configuration']['delete']);
        $this->assertArrayHasKey('PS_DELETE_2', $currentChanges['configuration']['delete']);
    }

    public function testConvertHandlesBooleanValues(): void
    {
        $change = $this->createMock(Change::class);
        $change->entity = 'configuration';
        $change->action = 'update';
        $change->key = 'PS_SHOP_ENABLE';
        $change->details = json_encode([
            'configuration' => [
                'key' => 'PS_SHOP_ENABLE',
                'values' => true,
            ],
        ]);

        $currentChanges = [];
        $this->converter->convert($change, $currentChanges);

        $this->assertTrue($currentChanges['configuration']['add_or_update']['PS_SHOP_ENABLE']);
    }

    public function testConvertHandlesNumericValues(): void
    {
        $change = $this->createMock(Change::class);
        $change->entity = 'configuration';
        $change->action = 'update';
        $change->key = 'PS_CART_RULE_FEATURE_ACTIVE';
        $change->details = json_encode([
            'configuration' => [
                'key' => 'PS_CART_RULE_FEATURE_ACTIVE',
                'values' => 1,
            ],
        ]);

        $currentChanges = [];
        $this->converter->convert($change, $currentChanges);

        $this->assertEquals(1, $currentChanges['configuration']['add_or_update']['PS_CART_RULE_FEATURE_ACTIVE']);
    }

    public function testConvertHandlesArrayValues(): void
    {
        $change = $this->createMock(Change::class);
        $change->entity = 'configuration';
        $change->action = 'update';
        $change->key = 'PS_MULTISHOP_FEATURE_ACTIVE';
        $change->details = json_encode([
            'configuration' => [
                'key' => 'PS_MULTISHOP_FEATURE_ACTIVE',
                'values' => ['shop1', 'shop2'],
            ],
        ]);

        $currentChanges = [];
        $this->converter->convert($change, $currentChanges);

        $this->assertIsArray($currentChanges['configuration']['add_or_update']['PS_MULTISHOP_FEATURE_ACTIVE']);
        $this->assertEquals(['shop1', 'shop2'], $currentChanges['configuration']['add_or_update']['PS_MULTISHOP_FEATURE_ACTIVE']);
    }
}
