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

use Configuration;
use PrestaShop\PrestaShop\Adapter\Module\AdminModuleDataProvider;
use PrestaShop\PrestaShop\Adapter\Module\Module;
use PrestaShop\PrestaShop\Core\Module\ModuleCollection;

/**
 * This class is not used yet as it works only on prestashop 8+
 * Work In Progress to check how we can use it on older versions too
 */
class ModuleDataProvider extends AdminModuleDataProvider
{
    /** @var string Configuration name which defines if modules upgrades are enable in back office */
    public const CONFIGURATION_NAME_DISABLE_BO_UPDATE = 'HHMODULESMANAGER_ENABLE_BO_MODULES_UPDATE';

    /**
     * @param ModuleCollection $modules
     * @param string|null $specific_action
     *
     * @return ModuleCollection
     */
    public function setActionUrls(ModuleCollection $modules, ?string $specific_action = null): ModuleCollection
    {
        if (!Configuration::get(self::CONFIGURATION_NAME_DISABLE_BO_UPDATE)) {
            //If module upgrade is disable in configuration, we remove the upgrade action
            if ($upgradeIndex = array_search(Module::ACTION_UPGRADE, $this->moduleActions)) {
                unset($this->moduleActions[$upgradeIndex]);
            }
        }

        return parent::setActionUrls($modules, $specific_action);
    }
}
