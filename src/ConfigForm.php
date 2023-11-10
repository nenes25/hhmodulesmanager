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
use Context;
use Exception;
use HelperForm;
use HhModulesManager;
use Language;
use Symfony\Component\Filesystem\Filesystem;
use Tools;

class ConfigForm
{
    /**
     * @var HhModulesManager
     */
    private $module;
    /**
     * @var string
     */
    private $configPrefix;
    /**
     * @var Context
     */
    private $context;

    /**
     * @param HhModulesManager $module
     * @param Context $context
     */
    public function __construct(HhModulesManager $module, Context $context)
    {
        $this->module = $module;
        $this->configPrefix = strtoupper($this->module->name) . '_';
        $this->context = $context;
    }

    /**
     * Manage configuration upgrade
     *
     * @return string|void
     */
    public function postProcess()
    {
        if (Tools::isSubmit('SubmitConfigForm')) {
            $enableUpdate = Tools::getValue($this->configPrefix . 'ENABLE_BO_MODULES_UPDATE');
            if (Configuration::updateValue(
                $this->configPrefix . 'ENABLE_BO_MODULES_UPDATE',
                $enableUpdate
            )
                && Configuration::updateValue(
                    $this->configPrefix . 'ENABLE_CLI_MODULES_UPDATE',
                    Tools::getValue($this->configPrefix . 'ENABLE_CLI_MODULES_UPDATE')
                )
                && Configuration::updateValue(
                    $this->configPrefix . 'ENABLE_CHANGE_RECORDER',
                    Tools::getValue($this->configPrefix . 'ENABLE_CHANGE_RECORDER')
                )
                && $this->toggleModuleUpdate((bool) $enableUpdate)) {
                return $this->module->displayConfirmation($this->l('Settings Updated'));
            } else {
                return $this->module->displayError($this->l('Unable to update settings'));
            }
        }
    }

    /**
     * Get configuration form
     *
     * @return string
     *
     * @throws Exception
     */
    public function renderForm(): string
    {
        $fields_form = [
            'form' => [
                'legend' => [
                    'title' => $this->l('Configure module management'),
                    'icon' => 'icon-cogs',
                ],
                'input' => [
                    [
                        'type' => 'switch',
                        'label' => $this->l('Enable module Upgrade'),
                        'name' => $this->configPrefix . 'ENABLE_BO_MODULES_UPDATE',
                        'hint' => $this->l('Allow to upgrade modules from back office ? (disabled by default by this module)'),
                        'required' => true,
                        'values' => [
                            ['id' => 'on', 'value' => 1, 'label' => $this->l('Yes')],
                            ['id' => 'off', 'value' => 0, 'label' => $this->l('No')],
                        ],
                    ],
                    [
                        'type' => 'switch',
                        'label' => $this->l('Enable module CLI Management'),
                        'name' => $this->configPrefix . 'ENABLE_CLI_MODULES_UPDATE',
                        'hint' => $this->l('Allow to upgrade modules from Cli by continous deployment ?'),
                        'required' => true,
                        'values' => [
                            ['id' => 'on', 'value' => 1, 'label' => $this->l('Yes')],
                            ['id' => 'off', 'value' => 0, 'label' => $this->l('No')],
                        ],
                    ],
                    [
                        'type' => 'switch',
                        'label' => $this->l('Enable Change recorder'),
                        'name' => $this->configPrefix . 'ENABLE_CHANGE_RECORDER',
                        'hint' => $this->l('Enable change recorder ? ( Only on dev environnement )'),
                        'required' => true,
                        'values' => [
                            ['id' => 'on', 'value' => 1, 'label' => $this->l('Yes')],
                            ['id' => 'off', 'value' => 0, 'label' => $this->l('No')],
                        ],
                    ],
                ],
                'submit' => [
                    'title' => $this->l('Save'),
                    'class' => 'button btn btn-default pull-right',
                ],
            ],
        ];

        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $lang = new Language((int) Configuration::get('PS_LANG_DEFAULT'));
        $helper->default_form_language = $lang->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
        $helper->identifier = $this->configPrefix;
        $helper->submit_action = 'SubmitConfigForm';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false) . '&configure=' . $this->module->name . '&tab_module=' . $this->module->tab . '&module_name=' . $this->module->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = [
            'fields_value' => $this->getFieldValues(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        ];

        return $helper->generateForm([$fields_form]);
    }

    /**
     * Get form configuration values
     *
     * @return array
     */
    protected function getFieldValues(): array
    {
        return [
            $this->configPrefix . 'ENABLE_BO_MODULES_UPDATE' => Configuration::get($this->configPrefix . 'ENABLE_BO_MODULES_UPDATE', Tools::getValue($this->configPrefix . 'ENABLE_BO_MODULES_UPDATE')),
            $this->configPrefix . 'ENABLE_CLI_MODULES_UPDATE' => Configuration::get($this->configPrefix . 'ENABLE_CLI_MODULES_UPDATE', Tools::getValue($this->configPrefix . 'ENABLE_CLI_MODULES_UPDATE')),
            $this->configPrefix . 'ENABLE_CHANGE_RECORDER' => Configuration::get($this->configPrefix . 'ENABLE_CHANGE_RECORDER', Tools::getValue($this->configPrefix . 'ENABLE_CHANGE_RECORDER')),
        ];
    }

    /**
     * Toggle Module Update by changing template name
     *
     * @param bool $enable
     *
     * @return bool
     */
    protected function toggleModuleUpdate($enable): bool
    {
        $fileSystem = new Filesystem();
        $updagradeTemplatePath = _PS_MODULE_DIR_ . $this->module->name . '/views/PrestaShop/Admin/Module/Includes/';

        if (true === $enable) {
            try {
                if ($fileSystem->exists($updagradeTemplatePath . 'action_button.html.twig')) {
                    $fileSystem->remove($updagradeTemplatePath . 'action_button.html.twig');
                }
                if (!$fileSystem->exists($updagradeTemplatePath . 'action_button.html.twig.disabled')) {
                    $fileSystem->copy(
                        $updagradeTemplatePath . 'action_button.html.twig.file',
                        $updagradeTemplatePath . 'action_button.html.twig.disabled'
                    );
                }

                return true;
            } catch (Exception $e) {
                return false;
            }
        }

        if (false === $enable) {
            try {
                if ($fileSystem->exists($updagradeTemplatePath . 'action_button.html.twig.disabled')) {
                    $fileSystem->remove($updagradeTemplatePath . 'action_button.html.twig.disabled');
                }
                if (!$fileSystem->exists($updagradeTemplatePath . 'action_button.html.twig')) {
                    $fileSystem->copy(
                        $updagradeTemplatePath . 'action_button.html.twig.file',
                        $updagradeTemplatePath . 'action_button.html.twig'
                    );
                }

                return true;
            } catch (Exception $e) {
                return false;
            }
        }
    }

    /**
     * Alias for translate function
     *
     * @param string $string
     *
     * @return string
     */
    public function l(string $string): string
    {
        return $this->module->l($string, 'configform');
    }
}
