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

use Db;
use Exception;
use ObjectModel;
use Validate;

class Change extends ObjectModel
{
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
     * Récupération de la liste des changements par filtres
     *
     * @param array $filters
     *
     * @return array
     */
    public static function getChangesByFilters(array $filters): array
    {
        try {
            $filters = array_filter($filters);
            if (count($filters)) {
                $changeQuery = (new \DbQuery())->select('id_change')
                    ->from(self::$definition['table']);
                foreach ($filters as $key => $filter) {
                    switch ($key) {
                        case 'from_date':
                            if (!Validate::isDate($filter)) {
                                throw new Exception('Invalid filter "from date"');
                            }
                            $changeQuery->where("date_add >='" . $filter . "' OR date_upd >= '" . $filter . "'");
                            break;
                        case 'to_date':
                            if (!Validate::isDate($filter)) {
                                throw new Exception('Invalid filter "to date"');
                            }
                            $changeQuery->where("date_add <='" . $filter . "' OR date_upd <= '" . $filter . "'");
                            break;
                        default:
                            if (false !== strpos($filter, ',')) {
                                $filterParts = explode(',', $filter);
                                $cleanAttributes = array_map(function ($item) {
                                    return "'" . pSQL(trim($item)) . "'";
                                }, $filterParts);
                                $cond = 'IN (' . implode(',', $cleanAttributes) . ')';
                            } else {
                                $cond = "= '" . $filter . "'";
                            }
                            $changeQuery->where($key . ' ' . $cond);
                            break;
                    }
                }
                $results = Db::getInstance()->executeS($changeQuery);
                if ($results) {
                    return array_column($results, 'id_change');
                }
            }
        } catch (Exception $e) {
            dump(__METHOD__ . ' ' . $e->getMessage());
        }

        return [];
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
                `details` TEXT NOT NULL,
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
