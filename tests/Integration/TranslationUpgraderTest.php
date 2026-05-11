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

use Doctrine\ORM\EntityManager;
use Hhennes\ModulesManager\Upgrader\Translation;
use PHPUnit\Framework\TestCase;
use PrestaShopBundle\Entity\Lang;
use PrestaShopBundle\Entity\Translation as TranslationEntity;

/**
 * Integration tests for Translation Upgrader
 *
 * These tests require a working PrestaShop installation with Doctrine EntityManager.
 * They test the actual behavior of applying translation changes to the database.
 */
class TranslationUpgraderTest extends TestCase
{
    /** @var Translation */
    private $upgrader;

    /** @var EntityManager */
    private $entityManager;

    /** @var int Default language ID (usually 1 for French or 2 for English) */
    private $defaultLangId;

    protected function setUp(): void
    {
        // Get EntityManager via the module which has access to Symfony container
        $module = \Module::getInstanceByName('hhmodulesmanager');
        if (!$module) {
            $this->markTestSkipped('hhmodulesmanager module not found or not installed');

            return;
        }

        try {
            $this->entityManager = $module->get('doctrine.orm.entity_manager');
        } catch (\Exception $e) {
            $this->markTestSkipped('EntityManager not available: ' . $e->getMessage());

            return;
        }

        $this->upgrader = new Translation($this->entityManager);

        // Get default language ID
        $lang = $this->entityManager->getRepository(Lang::class)->findOneBy(['active' => true]);
        $this->defaultLangId = $lang ? $lang->getId() : 1;
    }

    public function testUpgradeWithNoTranslationData(): void
    {
        $data = [
            'configuration' => [],
            'modules' => [],
        ];

        $this->upgrader->upgrade($data);

        $this->assertEmpty($this->upgrader->getErrors());
        $this->assertEmpty($this->upgrader->getSuccess());
    }

    public function testUpgradeWithEmptyTranslationSection(): void
    {
        $data = [
            'translation' => [],
        ];

        $this->upgrader->upgrade($data);

        $this->assertEmpty($this->upgrader->getErrors());
        $this->assertEmpty($this->upgrader->getSuccess());
    }

    public function testUpgradeCreatesNewTranslation(): void
    {
        $testKey = 'Test translation key ' . time();
        $testValue = 'Test translation value ' . time();
        $testDomain = 'TestDomain';

        $data = [
            'translation' => [
                'update' => [
                    'test-slug-' . time() => [
                        'id_lang' => $this->defaultLangId,
                        'domain' => $testDomain,
                        'key' => $testKey,
                        'translationValue' => $testValue,
                        'theme' => null,
                    ],
                ],
            ],
        ];

        $this->upgrader->upgrade($data);

        $success = $this->upgrader->getSuccess();
        $this->assertNotEmpty($success);
        $this->assertStringContainsString('updated successfully', $success[0]);
        $this->assertEmpty($this->upgrader->getErrors());

        // Verify the translation was created
        $lang = $this->entityManager->getRepository(Lang::class)->find($this->defaultLangId);
        $translation = $this->entityManager->getRepository(TranslationEntity::class)
            ->findOneBy([
                'lang' => $lang,
                'domain' => $testDomain,
                'key' => $testKey,
            ]);

        $this->assertNotNull($translation);
        $this->assertEquals($testValue, $translation->getTranslation());

        // Cleanup
        $this->entityManager->remove($translation);
        $this->entityManager->flush();
    }

    public function testUpgradeUpdatesExistingTranslation(): void
    {
        $testKey = 'Existing translation key ' . time();
        $initialValue = 'Initial value';
        $updatedValue = 'Updated value ' . time();
        $testDomain = 'TestDomain';

        // Create initial translation
        $lang = $this->entityManager->getRepository(Lang::class)->find($this->defaultLangId);
        $translation = new TranslationEntity();
        $translation->setLang($lang);
        $translation->setKey($testKey);
        $translation->setDomain($testDomain);
        $translation->setTheme(null);
        $translation->setTranslation($initialValue);

        $this->entityManager->persist($translation);
        $this->entityManager->flush();

        // Now update it via upgrader
        $data = [
            'translation' => [
                'update' => [
                    'update-slug-' . time() => [
                        'id_lang' => $this->defaultLangId,
                        'domain' => $testDomain,
                        'key' => $testKey,
                        'translationValue' => $updatedValue,
                        'theme' => null,
                    ],
                ],
            ],
        ];

        $this->upgrader->upgrade($data);

        $success = $this->upgrader->getSuccess();
        $this->assertNotEmpty($success);
        $this->assertEmpty($this->upgrader->getErrors());

        // Verify the translation was updated
        $this->entityManager->refresh($translation);
        $this->assertEquals($updatedValue, $translation->getTranslation());

        // Cleanup
        $this->entityManager->remove($translation);
        $this->entityManager->flush();
    }

    public function testUpgradeDeletesTranslation(): void
    {
        $testKey = 'Translation to delete ' . time();
        $testValue = 'Value to delete';
        $testDomain = 'TestDomain';

        // Create translation to delete
        $lang = $this->entityManager->getRepository(Lang::class)->find($this->defaultLangId);
        $translation = new TranslationEntity();
        $translation->setLang($lang);
        $translation->setKey($testKey);
        $translation->setDomain($testDomain);
        $translation->setTheme(null);
        $translation->setTranslation($testValue);

        $this->entityManager->persist($translation);
        $this->entityManager->flush();
        $translationId = $translation->getId();

        // Delete via upgrader
        $data = [
            'translation' => [
                'delete' => [
                    'delete-slug-' . time() => [
                        'id_lang' => $this->defaultLangId,
                        'domain' => $testDomain,
                        'key' => $testKey,
                        'theme' => null,
                    ],
                ],
            ],
        ];

        $this->upgrader->upgrade($data);

        $success = $this->upgrader->getSuccess();
        $this->assertNotEmpty($success);
        $this->assertStringContainsString('deleted successfully', $success[0]);
        $this->assertEmpty($this->upgrader->getErrors());

        // Verify the translation was deleted
        $deletedTranslation = $this->entityManager->getRepository(TranslationEntity::class)
            ->find($translationId);
        $this->assertNull($deletedTranslation);
    }

    public function testUpgradeErrorsWhenDeletingNonExistentTranslation(): void
    {
        $data = [
            'translation' => [
                'delete' => [
                    'nonexistent-' . time() => [
                        'id_lang' => $this->defaultLangId,
                        'domain' => 'NonExistentDomain',
                        'key' => 'NonExistentKey' . time(),
                        'theme' => null,
                    ],
                ],
            ],
        ];

        $this->upgrader->upgrade($data);

        $errors = $this->upgrader->getErrors();
        $this->assertNotEmpty($errors);
        $this->assertStringContainsString('not found or already deleted', $errors[0]);
        $this->assertEmpty($this->upgrader->getSuccess());
    }

    public function testUpgradeHandlesMultipleTranslations(): void
    {
        $testKey1 = 'Multi test 1 ' . time();
        $testKey2 = 'Multi test 2 ' . time();
        $testValue1 = 'Value 1 ' . time();
        $testValue2 = 'Value 2 ' . time();
        $testDomain = 'TestDomain';

        $data = [
            'translation' => [
                'update' => [
                    'multi1-' . time() => [
                        'id_lang' => $this->defaultLangId,
                        'domain' => $testDomain,
                        'key' => $testKey1,
                        'translationValue' => $testValue1,
                        'theme' => null,
                    ],
                    'multi2-' . time() => [
                        'id_lang' => $this->defaultLangId,
                        'domain' => $testDomain,
                        'key' => $testKey2,
                        'translationValue' => $testValue2,
                        'theme' => null,
                    ],
                ],
            ],
        ];

        $this->upgrader->upgrade($data);

        $success = $this->upgrader->getSuccess();
        $this->assertCount(2, $success);
        $this->assertEmpty($this->upgrader->getErrors());

        // Verify both translations were created
        $lang = $this->entityManager->getRepository(Lang::class)->find($this->defaultLangId);
        $translation1 = $this->entityManager->getRepository(TranslationEntity::class)
            ->findOneBy(['lang' => $lang, 'domain' => $testDomain, 'key' => $testKey1]);
        $translation2 = $this->entityManager->getRepository(TranslationEntity::class)
            ->findOneBy(['lang' => $lang, 'domain' => $testDomain, 'key' => $testKey2]);

        $this->assertNotNull($translation1);
        $this->assertNotNull($translation2);
        $this->assertEquals($testValue1, $translation1->getTranslation());
        $this->assertEquals($testValue2, $translation2->getTranslation());

        // Cleanup
        $this->entityManager->remove($translation1);
        $this->entityManager->remove($translation2);
        $this->entityManager->flush();
    }

    public function testResetResults(): void
    {
        $testKey = 'Reset test ' . time();
        $testValue = 'Reset value';
        $testDomain = 'TestDomain';

        $data = [
            'translation' => [
                'update' => [
                    'reset-' . time() => [
                        'id_lang' => $this->defaultLangId,
                        'domain' => $testDomain,
                        'key' => $testKey,
                        'translationValue' => $testValue,
                        'theme' => null,
                    ],
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
        $lang = $this->entityManager->getRepository(Lang::class)->find($this->defaultLangId);
        $translation = $this->entityManager->getRepository(TranslationEntity::class)
            ->findOneBy(['lang' => $lang, 'domain' => $testDomain, 'key' => $testKey]);
        if ($translation) {
            $this->entityManager->remove($translation);
            $this->entityManager->flush();
        }
    }
}
