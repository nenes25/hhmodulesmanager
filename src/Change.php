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

namespace Hhennes\ModulesManager;

use Configuration;
use Db;
use Hhennes\ModulesManager\Converter\ConfigurationConverter;
use Hhennes\ModulesManager\Converter\ModuleConverter;
use ObjectModel;
use Symfony\Component\Yaml\Yaml;

class Change extends ObjectModel
{
    /** @var string Entité configuration */
    public const CHANGE_ENTITY_CONFIGURATION = 'configuration';
    /** @var string Entité module */
    public const CHANGE_ENTITY_MODULE = 'module';

    /** @var string Nom du module */
    public const MODULE_NAME = 'hhmodulesmanager';

    /** @var int Object id */
    public $id;
    /** @var string Nom de l'entité */
    public $entity;
    /** @var string Nom de l'action */
    public $action;
    /** @var string Nom de la clé (pour les configurations) */
    public $key;
    /** @var string Détails de l'action */
    public $details;
    /** @var string Date de création */
    public $date_add;
    /** @var string Date de mise à jour */
    public $date_upd;

    /**
     * {@inheritdoc}
     */
    public static $definition = [
        'table' => 'hhmodulesmanager_change',
        'primary' => 'id_change',
        'fields' => [
            'entity' => ['type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'length' => 255],
            'action' => ['type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'length' => 100],
            'key' => ['type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'length' => 100],
            'details' => ['type' => self::TYPE_STRING],
            'date_add' => ['type' => self::TYPE_DATE],
            'date_upd' => ['type' => self::TYPE_DATE],
        ],
    ];

    /**
     * Génération d'un fichier de changements
     *
     * @param array $changeIds
     * @param string $changeVersion
     *
     * @return string
     *
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public static function generateChangeFile(array $changeIds, string $changeVersion): string
    {
        $orderedChanges = self::generateChangeFileArray($changeIds);
        $fileName = self::getUpgradePath() . $changeVersion . '.yml';
        $yaml = Yaml::dump($orderedChanges, 3);
        file_put_contents(
            $fileName,
            $yaml
        );

        return $fileName;
    }

    /**
     * Récupération du chemin des upgrades
     *
     * @return string
     */
    public static function getUpgradePath(): string
    {
        return _PS_MODULE_DIR_ . self::MODULE_NAME . '/upgrades/';
    }

    /**
     * Génération du tableau récapitulatif des changements
     *
     * @param array $changeIds
     *
     * @return array
     *
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public static function generateChangeFileArray(array $changeIds): array
    {
        $currentChanges = [];
        $converters = [];
        foreach ($changeIds as $changeId) {
            $change = new Change($changeId);
            $changeIsProcessed = false;
            try {
                if ($change->entity == self::CHANGE_ENTITY_CONFIGURATION) {
                    if (!array_key_exists(self::CHANGE_ENTITY_CONFIGURATION, $converters)) {
                        $converters[self::CHANGE_ENTITY_CONFIGURATION] = new ConfigurationConverter();
                    }
                    $converters[self::CHANGE_ENTITY_CONFIGURATION]->convert($change, $currentChanges);
                    $changeIsProcessed = true;
                }

                if ($change->entity == self::CHANGE_ENTITY_MODULE) {
                    if (!array_key_exists(self::CHANGE_ENTITY_MODULE, $converters)) {
                        $converters[self::CHANGE_ENTITY_MODULE] = new ModuleConverter();
                    }
                    $converters[self::CHANGE_ENTITY_MODULE]->convert($change, $currentChanges);
                    $changeIsProcessed = true;
                }
                if (true === $changeIsProcessed) {
                    $change->delete();
                }
            } catch (\Exception $e) {
                echo $e->getMessage();
            }
        }

        return $currentChanges;
    }

    /**
     * Installation sql de l'entité
     *
     * @return bool
     */
    public static function installSql()
    {
        return Db::getInstance()->execute(
            'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'hhmodulesmanager_change`(
                `id_change` int(10) NOT NULL AUTO_INCREMENT,
                `entity` VARCHAR (255) NOT NULL,
                `action` VARCHAR (100) NOT NULL,
                `key` VARCHAR (100) NULL,
                `details` VARCHAR (255) NOT NULL,
                `date_add` datetime NOT NULL,
                `date_upd` datetime NOT NULL,
                PRIMARY KEY (`id_change`) )
                ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=UTF8;'
        );
    }

    /**
     * Désinstallation sql de l'entité
     *
     * @return bool
     */
    public static function uninstallSql()
    {
        return Db::getInstance()->execute(
            'DROP TABLE IF EXISTS ' . _DB_PREFIX_ . 'hhmodulesmanager_change'
        );
    }
}
