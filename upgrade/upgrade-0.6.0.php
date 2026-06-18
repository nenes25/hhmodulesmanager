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
if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Upgrade module to version 0.6.0
 * Migrate legacy admin controller tab to Symfony admin grid tab
 *
 * @param HhModulesManager $module
 *
 * @return bool
 */
function upgrade_module_0_6_0(HhModulesManager $module): bool
{
    // Remove the legacy tab registered with class_name 'change'
    $oldTabId = Tab::getIdFromClassName('change');
    if ($oldTabId) {
        $tab = new Tab($oldTabId);
        $tab->delete();
    }

    // Install the new Symfony-backed tab
    $tab = new Tab();
    $tab->class_name = 'AdminHhmodulesmanagerChange';
    $tab->route_name = 'admin_hhmodulesmanager_change_list';
    $tab->module = $module->name;
    $tab->id_parent = Tab::getIdFromClassName('AdminParentModulesSf');
    foreach (Language::getLanguages() as $lang) {
        $tab->name[$lang['id_lang']] = $module->l('Module Manager Changes');
    }

    try {
        return (bool) $tab->save();
    } catch (Exception $e) {
        return false;
    }
}
