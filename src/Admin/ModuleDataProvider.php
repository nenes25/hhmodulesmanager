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
namespace Hhennes\ModulesManager\Admin;

use \Configuration;
use PrestaShop\PrestaShop\Adapter\Module\AdminModuleDataProvider;
use PrestaShop\PrestaShop\Core\Module\ModuleCollection;
use PrestaShop\PrestaShop\Adapter\Module\Module;

class ModuleDataProvider extends AdminModuleDataProvider
{

    public const CONFIGURATION_NAME_DISABLE_BO_UPDATE = 'HHMODULESMANAGER_ENABLE_BO_MODULES_UPDATE';

    /**
     * @param ModuleCollection $modules
     * @param string|null $specific_action
     *
     * @return ModuleCollection
     */
    public function setActionUrls(ModuleCollection $modules, ?string $specific_action = null): ModuleCollection
    {
        if ( !Configuration::get(self::CONFIGURATION_NAME_DISABLE_BO_UPDATE)) {
            //Dans le cas ou la mise à jour des modules est désactivée dans l'admin on retire l'action
            if ($upgradeIndex = array_search(Module::ACTION_UPGRADE, $this->moduleActions)) {
                unset($this->moduleActions[$upgradeIndex]);
            }
        }
        return parent::setActionUrls($modules,$specific_action);
    }
}