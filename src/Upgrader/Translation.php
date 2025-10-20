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

namespace Hhennes\ModulesManager\Upgrader;

use Doctrine\ORM\EntityManager;
use PrestaShopBundle\Entity\Lang;
use PrestaShopBundle\Entity\Translation as TranslationEntity;

class Translation implements UpgraderInterface
{
    use UpgraderResultTrait;

    /** @var string Upgrader type */
    public const TYPE = 'translation';

    /** @var EntityManager */
    protected $entityManager;

    /**
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param array $data
     */
    public function upgrade(array $data): void
    {
        if (!array_key_exists(self::TYPE, $data)) {
            return;
        }
        $data = $data[self::TYPE];

        // Add or update translations
        if (array_key_exists('update', $data)
            && is_array($data['update'])
            && count($data['update'])
        ) {
            foreach ($data['update'] as $key => $details) {
                try {
                    $this->updateTranslation($details);
                    $this->success[] = 'Translation "' . $key . '" updated successfully';
                } catch (\Exception $e) {
                    $this->errors[] = 'Unable to update translation "' . $key . '": ' . $e->getMessage();
                }
            }
        }

        // Delete translations
        if (array_key_exists('delete', $data)
            && is_array($data['delete'])
            && count($data['delete'])
        ) {
            foreach ($data['delete'] as $key => $details) {
                try {
                    $this->deleteTranslation($details);
                    $this->success[] = 'Translation "' . $key . '" deleted successfully';
                } catch (\Exception $e) {
                    $this->errors[] = 'Unable to delete translation "' . $key . '": ' . $e->getMessage();
                }
            }
        }
    }

    /**
     * Update or create a translation
     *
     * @param array $details Translation details (id_lang, domain, key, translationValue, theme)
     *
     * @throws \Exception
     */
    protected function updateTranslation(array $details): void
    {
        // Get the Lang entity
        $lang = $this->entityManager->getRepository(Lang::class)->find($details['id_lang']);
        if (!$lang) {
            throw new \Exception('Language with ID ' . $details['id_lang'] . ' not found');
        }

        // Find existing translation
        $queryBuilder = $this->entityManager->getRepository(TranslationEntity::class)
            ->createQueryBuilder('t')
            ->where('t.lang = :lang')->setParameter('lang', $lang)
            ->andWhere('t.domain = :domain')->setParameter('domain', $details['domain'])
            ->andWhere('t.key LIKE :key')->setParameter('key', $details['key']);

        if (!empty($details['theme'])) {
            $queryBuilder->andWhere('t.theme = :theme')->setParameter('theme', $details['theme']);
        } else {
            $queryBuilder->andWhere('t.theme IS NULL');
        }

        $translation = null;
        try {
            $translation = $queryBuilder->getQuery()->getSingleResult();
        } catch (\Exception $e) {
            // Translation doesn't exist, we'll create a new one
        }

        if (null === $translation) {
            // Create new translation
            $translation = new TranslationEntity();
            $translation->setLang($lang);
            $translation->setKey($details['key']);
            $translation->setDomain($details['domain']);
            $translation->setTheme(!empty($details['theme']) ? $details['theme'] : null);
        }

        // Update translation value
        $translation->setTranslation($details['translationValue']);

        $this->entityManager->persist($translation);
        $this->entityManager->flush();
    }

    /**
     * Delete a translation
     *
     * @param array $details Translation details (id_lang, domain, key, theme)
     *
     * @throws \Exception
     */
    protected function deleteTranslation(array $details): void
    {
        // Get the Lang entity
        $lang = $this->entityManager->getRepository(Lang::class)->find($details['id_lang']);
        if (!$lang) {
            throw new \Exception('Language with ID ' . $details['id_lang'] . ' not found');
        }

        // Find existing translation
        $queryBuilder = $this->entityManager->getRepository(TranslationEntity::class)
            ->createQueryBuilder('t')
            ->where('t.lang = :lang')->setParameter('lang', $lang)
            ->andWhere('t.domain = :domain')->setParameter('domain', $details['domain'])
            ->andWhere('t.key LIKE :key')->setParameter('key', $details['key']);

        if (!empty($details['theme'])) {
            $queryBuilder->andWhere('t.theme = :theme')->setParameter('theme', $details['theme']);
        } else {
            $queryBuilder->andWhere('t.theme IS NULL');
        }

        try {
            $translation = $queryBuilder->getQuery()->getSingleResult();
            $this->entityManager->remove($translation);
            $this->entityManager->flush();
        } catch (\Exception $e) {
            throw new \Exception('Translation not found or already deleted');
        }
    }
}
