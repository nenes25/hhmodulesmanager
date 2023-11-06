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
use HhModulesManager;
use Language;
use Tab;

class Installer
{
    /**
     * @todo Refaire l'installation de la tab
     */

    /**
     * @var HhModulesManager Instance du module
     */
    protected $module;

    /**
     * @var array Liste des hooks du module
     */
    protected $_hooks = [
        'actionModuleInstallAfter',
        'actionModuleUnInstallAfter',
        'actionModuleUpgradeVersion',
        'actionConfigurationUpdateValue',
        'actionConfigurationDeleteKey',
        'actionConfigurationDeleteContextKey',
    ];
    /**
     * @var string
     */
    protected $configPrefix;

    /**
     * @param HhModulesManager $module
     */
    public function __construct(HhModulesManager $module)
    {
        $this->module = $module;
        $this->configPrefix = strtoupper($this->module->name) . '_';
    }

    /**
     * Installation du module
     *
     * @return bool
     */
    public function install(): bool
    {
        return
            $this->module->registerHook($this->_hooks)
            && Change::installSql()
            && $this->installPatchSql()
            && $this->installTab()
            && Configuration::updateGlobalValue($this->configPrefix . 'ENABLE_BO_MODULES_UPDATE', 0)
            && Configuration::updateGlobalValue($this->configPrefix . 'ENABLE_CLI_MODULES_UPDATE', 1);
    }

    /**
     * Désinstallation du module
     *
     * @return bool
     */
    public function uninstall(): bool
    {
        return Change::uninstallSql()
            && $this->uninstallPatchSql()
            && $this->uninstallConfiguration();
    }

    /**
     * Création d'une tab pour le controller Admin
     *
     * @return bool
     */
    protected function installTab(): bool
    {
        $tab = new Tab();
        $tab->class_name = 'change';
        $tab->module = $this->module->name;
        $tab->id_parent = Tab::getIdFromClassName('AdminAdvancedParameters');
        foreach (Language::getLanguages() as $lang) {
            $tab->name[$lang['id_lang']] = $this->module->l('Module Manager Changes');
        }
        try {
            $tab->save();
        } catch (\Exception $e) {
            dump($e->getMessage());

            return false;
        }

        return true;
    }

    /**
     * Installation de la table des patchs
     *
     * @return bool
     */
    protected function installPatchSql(): bool
    {
        return Db::getInstance()->execute(
            'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'hhmodulesmanager_patches`(
                `id_patch` int(10) NOT NULL AUTO_INCREMENT,
                `name` VARCHAR (255) NOT NULL,
                `date_add` DATETIME DEFAULT CURRENT_TIMESTAMP,
                `date_upd` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`id_patch`) )
                ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=UTF8;'
        );
    }

    /**
     * Suppression de la table des patchs
     *
     * @return bool
     */
    protected function uninstallPatchSql(): bool
    {
        return Db::getInstance()->execute('DROP TABLE IF EXISTS ' . _DB_PREFIX_ . 'hhmodulesmanager_patches');
    }

    /**
     * Désinstallation de la configuration
     *
     * @return bool
     */
    protected function uninstallConfiguration(): bool
    {
        return Configuration::deleteByName($this->configPrefix . 'ENABLE_BO_MODULES_UPDATE')
            && Configuration::deleteByName($this->configPrefix . 'ENABLE_CLI_MODULES_UPDATE')
            && Configuration::deleteByName($this->configPrefix . 'ENABLE_CHANGE_RECORDER');
    }
}
