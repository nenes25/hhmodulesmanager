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

namespace Hhennes\ModulesManager\Patch;

use Db;
use Hhennes\ModulesManager\Upgrader\UpgraderFactory;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\Yaml\Parser;

class Manager
{
    protected array $errors = [];
    protected array $warnings = [];
    protected array $success = [];
    private UpgraderFactory $upgraderFactory;

    /**
     * @param UpgraderFactory $upgraderFactory
     */
    public function __construct(
        UpgraderFactory $upgraderFactory
    ) {
        $this->upgraderFactory = $upgraderFactory;
    }

    /**
     * Apply a patch
     *
     * @param string $upgradeFile
     * @param string $patchName
     *
     * @return void
     */
    public function applyPatch($upgradeFile, string $patchName): void
    {
        $data = $this->parseUpgradeFile($upgradeFile);
        $this->processUpgrade($data);
        $this->registerAppliedPatch($patchName);
    }

    /**
     * Analyze and process the upgrade file
     *
     * @param SplFileInfo $file
     *
     * @return array|mixed|\stdClass|\Symfony\Component\Yaml\Tag\TaggedValue|null
     */
    protected function parseUpgradeFile(SplFileInfo $file)
    {
        try {
            $parser = new Parser();

            return $parser->parseFile($file->getPathname());
        } catch (\Exception $e) {
            $this->errors[] = 'Unable to parse configuration';

            return [];
        }
    }

    /**
     * Run the upgrade
     *
     * @param array $data
     *
     * @return void
     */
    protected function processUpgrade(array $data): void
    {
        foreach ($this->upgraderFactory->getUpgraders() as $upgrader) {
            try {
                $upgrader->upgrade($data);
                $this->errors = array_merge($this->errors, $upgrader->getErrors());
                $this->success = array_merge($this->success, $upgrader->getSuccess());
            } catch (\Exception $e) {
                $this->errors[] = 'Unable to upgrade data for upgrader ' . get_class($upgrader) .
                    ' error : ' . $e->getMessage();
            }
        }
    }

    /**
     * Get the list of upgrades files
     *
     * @return Finder
     */
    public function getUpgradeFiles(): Finder
    {
        $finder = new Finder();

        return $finder->files()
            ->in($this->getUpgradeDirectory())
            ->sortByName()
            ->name('*.yml');
    }

    /**
     * Get the directory where the upgrades files are stored
     *
     * @return string
     */
    public function getUpgradeDirectory(): string
    {
        return _PS_MODULE_DIR_ . 'hhmodulesmanager/upgrades/';
    }

    /**
     * Get the list of already applied patches
     *
     * @return array
     */
    public function getAppliedPatches(): array
    {
        try {
            $patches = Db::getInstance()->executeS(
                'SELECT name FROM `' . _DB_PREFIX_ . 'hhmodulesmanager_patches`'
            );
            if ($patches && count($patches)) {
                return array_column($patches, 'name');
            }
        } catch (\Exception $e) {
            $this->errors[] = 'Unable to get Applied patches lists ' . $e->getMessage();
        }

        return [];
    }

    /**
     * Register the application of a patch
     *
     * @param string $patchName
     *
     * @return void
     *
     * @throws \PrestaShopDatabaseException
     */
    public function registerAppliedPatch(string $patchName): void
    {
        //Gestion des mises à jours
        $idPatch = Db::getInstance()->getValue(
            'SELECT id_patch 
                    FROM ' . _DB_PREFIX_ . "hhmodulesmanager_patches
                    WHERE name ='" . pSQL($patchName) . "'"
        );
        if ($idPatch) {
            Db::getInstance()->update(
                'hhmodulesmanager_patches',
                ['name' => pSQL($patchName)],
                'id_patch=' . (int) $idPatch
            );
        } else {
            Db::getInstance()->insert(
                'hhmodulesmanager_patches',
                ['name' => pSQL($patchName)]
            );
        }
    }

    /**
     * Get errors messages
     *
     * @return array
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Get warning messages
     *
     * @return array
     */
    public function getWarnings(): array
    {
        return $this->warnings;
    }

    /**
     * Get success messages
     *
     * @return array
     */
    public function getSuccess(): array
    {
        return $this->success;
    }
}
