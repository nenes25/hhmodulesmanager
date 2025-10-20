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
use Hhennes\ModulesManager\Converter\Translation;
use PHPUnit\Framework\TestCase;

class TranslationTest extends TestCase
{
    /** @var Translation */
    private $converter;

    protected function setUp(): void
    {
        $this->converter = new Translation();
    }

    public function testCanConvertReturnsTrueForTranslationEntity(): void
    {
        // Create a mock Change object
        $change = $this->createMock(Change::class);
        $change->entity = 'translation';

        $result = $this->converter->canConvert($change);

        $this->assertTrue($result);
    }

    public function testCanConvertReturnsFalseForNonTranslationEntity(): void
    {
        $change = $this->createMock(Change::class);
        $change->entity = 'configuration';

        $result = $this->converter->canConvert($change);

        $this->assertFalse($result);
    }

    public function testConvertAddsTranslationUpdateToArray(): void
    {
        $change = $this->createMock(Change::class);
        $change->entity = 'translation';
        $change->action = 'update';
        $change->key = 'test-slug';
        $change->details = json_encode([
            'slug' => 'test-slug',
            'id_lang' => 1,
            'domain' => 'ShopThemeCatalog',
            'key' => 'Test Translation',
            'translationValue' => 'Test Traduction',
            'theme' => null,
        ]);

        $currentChanges = [];
        $this->converter->convert($change, $currentChanges);

        $this->assertArrayHasKey('translation', $currentChanges);
        $this->assertArrayHasKey('update', $currentChanges['translation']);
        $this->assertArrayHasKey('test-slug', $currentChanges['translation']['update']);

        $translationData = $currentChanges['translation']['update']['test-slug'];
        $this->assertEquals(1, $translationData['id_lang']);
        $this->assertEquals('ShopThemeCatalog', $translationData['domain']);
        $this->assertEquals('Test Translation', $translationData['key']);
        $this->assertEquals('Test Traduction', $translationData['translationValue']);
        $this->assertNull($translationData['theme']);
    }

    public function testConvertAddsTranslationDeleteToArray(): void
    {
        $change = $this->createMock(Change::class);
        $change->entity = 'translation';
        $change->action = 'delete';
        $change->key = 'deleted-slug';
        $change->details = json_encode([
            'slug' => 'deleted-slug',
            'id_lang' => 2,
            'domain' => 'ShopThemeCatalog',
            'key' => 'Deleted Translation',
            'theme' => 'classic',
        ]);

        $currentChanges = [];
        $this->converter->convert($change, $currentChanges);

        $this->assertArrayHasKey('translation', $currentChanges);
        $this->assertArrayHasKey('delete', $currentChanges['translation']);
        $this->assertArrayHasKey('deleted-slug', $currentChanges['translation']['delete']);
    }

    public function testConvertThrowsExceptionForInvalidAction(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Unknow translation action');

        $change = $this->createMock(Change::class);
        $change->entity = 'translation';
        $change->action = 'invalid_action';
        $change->key = 'test-slug';
        $change->details = '{}';

        $currentChanges = [];
        $this->converter->convert($change, $currentChanges);
    }

    public function testConvertMergesMultipleUpdates(): void
    {
        // First change
        $change1 = $this->createMock(Change::class);
        $change1->entity = 'translation';
        $change1->action = 'update';
        $change1->key = 'slug-1';
        $change1->details = json_encode([
            'slug' => 'slug-1',
            'id_lang' => 1,
            'domain' => 'Domain1',
            'key' => 'Key 1',
            'translationValue' => 'Value 1',
            'theme' => null,
        ]);

        // Second change
        $change2 = $this->createMock(Change::class);
        $change2->entity = 'translation';
        $change2->action = 'update';
        $change2->key = 'slug-2';
        $change2->details = json_encode([
            'slug' => 'slug-2',
            'id_lang' => 2,
            'domain' => 'Domain2',
            'key' => 'Key 2',
            'translationValue' => 'Value 2',
            'theme' => 'classic',
        ]);

        $currentChanges = [];
        $this->converter->convert($change1, $currentChanges);
        $this->converter->convert($change2, $currentChanges);

        $this->assertCount(2, $currentChanges['translation']['update']);
        $this->assertArrayHasKey('slug-1', $currentChanges['translation']['update']);
        $this->assertArrayHasKey('slug-2', $currentChanges['translation']['update']);
    }
}
