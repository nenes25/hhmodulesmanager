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
include dirname(__FILE__) . '/vendor/autoload.php';

use Hhennes\ModulesManager\Change;
use Hhennes\ModulesManager\ConfigForm;
use Hhennes\ModulesManager\Installer;
use Psr\Log\LoggerInterface;

class HhModulesManager extends Module
{
    /**
     * List of ignored configuration
     */
    public const EXCLUDED_CONFIGURATIONS = [
        'HHMODULESMANAGER_ENABLE_CHANGE_RECORDER',
        'PS_CCCJS_VERSION',
        'PS_CCCCSS_VERSION',
    ];

    /** @var LoggerInterface|null */
    protected $logger = null;

    public function __construct()
    {
        $this->name = 'hhmodulesmanager';
        $this->tab = 'administration';
        $this->version = '0.4.1';
        $this->author = 'hhennes';
        $this->bootstrap = true;
        parent::__construct();

        $this->displayName = $this->l('Enhanced modules management');
        $this->description = $this->l('Enhanced modules management through cli and CI/CD');
    }

    /**
     * Install Module
     *
     * @return bool
     */
    public function install(): bool
    {
        $installer = new Installer($this);

        return parent::install()
            && $installer->install();
    }

    /**
     * Uninstall Module
     *
     * @return bool
     */
    public function uninstall(): bool
    {
        $installer = new Installer($this);

        return parent::uninstall()
            && $installer->uninstall();
    }

    /**
     * Hook executed after a module install
     *
     * @param array $params
     *
     * @return void
     */
    public function hookActionModuleInstallAfter(array $params): void
    {
        /** @var Module $module */
        $module = $params['object'];
        $this->logEvent('module', 'install', $module->name, ['name' => $module->name]);
    }

    /**
     * Hook executed after a module uninstall
     *
     * @param array $params
     *
     * @return void
     */
    public function hookActionModuleUnInstallAfter(array $params): void
    {
        /** @var Module $module */
        $module = $params['object'];
        $this->logEvent('module', 'uninstall', $module->name, ['name' => $module->name]);
    }

    /**
     * Hook (custom) to listen to module upgrades
     *
     * @param array $params
     *
     * @return void
     */
    public function hookActionModuleUpgradeVersion(array $params): void
    {
        $this->logEvent(
            'module',
            'update',
            $params['module'],
            ['name' => $params['module'], 'version' => $params['version']]
        );
    }

    /**
     * Hook (custom) executed BEFORE updating a configuration
     *
     * So we can check if the configuration have changed or not
     *
     * @param array $params
     */
    public function hookActionConfigurationUpdateValue(array $params): void
    {
        $configurationKey = $params['configuration']['key'];
        $configurationValue = $params['configuration']['values'];
        $idShop = $params['configuration']['idShop'];
        $idShopGroup = $params['configuration']['idShopGroup'];
        if (!$this->isExludedConfiguration($configurationKey)) {
            if (($oldValue = Configuration::get($configurationKey, null, $idShopGroup, $idShop)) != $configurationValue) {
                $this->logEvent('configuration', 'update', $configurationKey, ['configuration' => $params['configuration'], 'old_value' => $oldValue]);
            }
        }
    }

    /**
     * Hook (custom) executed BEFORE deleting a configuration
     *
     * @param array $params
     */
    public function hookActionConfigurationDeleteKey(array $params): void
    {
        if (!$this->isExludedConfiguration($params['key'])) {
            $this->logEvent('configuration', 'delete', $params['key'], ['name' => $params['key']]);
        }
    }

    /**
     * Hook (custom) executed BEFORE deleting a configuration in a specific context
     *
     * @param array $params
     */
    public function hookActionConfigurationDeleteContextKey(array $params): void
    {
        if (!$this->isExludedConfiguration($params['key'])) {
            $this->logEvent(
                'configuration',
                'delete',
                null,
                ['name' => $params['key'], 'idShop' => $params['idShop'], 'idShopGroup' => $params['idShopGroup']]
            );
        }
    }

    /**
     * Hook (custom) in back office, in the module listing page.
     *
     * Allow to display a warning about the disabling of module update
     *
     * @param array $params
     *
     * @return string
     */
    public function hookDisplayAdminModulesListHeader(array $params): string
    {
        if (!Configuration::get('HHMODULESMANAGER_ENABLE_BO_MODULES_UPDATE')) {
            return '<div class="alert alert-warning align-content-center">' .
                $this->l('Modules upgrades are disabled into the back office on this environnement.') . '<br />' .
                $this->l('Please check hhmodulesmanager configuration  if you need to update them') .
                '</div>';
        }

        return '';
    }

    /**
     * Define if recording of events is enabled
     *
     * @return bool
     */
    public function isRecorderEnabled(): bool
    {
        return (bool) Configuration::get(strtoupper($this->name) . '_ENABLE_CHANGE_RECORDER');
    }

    /**
     * Define if configuration is excluded from recording
     *
     * @param string $key
     *
     * @return bool
     */
    protected function isExludedConfiguration($key): bool
    {
        $excludedConfigurations = [];
        Hook::exec(
            'actionHhmodulesmanagerExcludeConfiguration',
            ['configuration' => &$excludedConfigurations],
            null,
            true
        );
        $excludedConfigurations = array_merge($excludedConfigurations, self::EXCLUDED_CONFIGURATIONS);

        return in_array($key, $excludedConfigurations);
    }

    /**
     * Log the event change
     *
     * @param string $name Event name
     * @param string $type Event type
     * @param string|null $key Event key
     * @param array $details Event array of details
     */
    protected function logEvent(string $name, string $type, ?string $key = null, array $details = []): void
    {
        if (!$this->isRecorderEnabled()) {
            return;
        }

        try {
            $change = new Change();
            $change->entity = $name;
            $change->action = $type;
            $change->key = $key;
            $change->details = json_encode($details);
            $change->add();
            $message = date('Y-m-d H:i:s') . ' : '
                . $name . ' ' . $type . ' ' . json_encode($details) . "\n";

            $this->log($message);
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

    /**
     * Log data with monolog
     *
     * @param string $message
     * @param int $level
     *
     * @return void
     */
    public function log(string $message, int $level = 3): void
    {
        try {
            $logger = $this->getLogger();
            if ($logger) {
                $logger->log($level * 100, $message);
            } else {
                //In some context the logger might not be defined due to missing symfony context
                //So we make a fallback
                file_put_contents(
                    _PS_ROOT_DIR_ . '/var/logs/' . $this->name . '/' . $this->name . '.log',
                    '['.date('Y-m-d H:i:s') . '] - '.$this->name .'.'. $level . ' - ' . $message . "\n",
                    FILE_APPEND
                );
            }
        } catch (Exception $e) {
            file_put_contents(
                dirname(__FILE__) . '/logs/exceptions.log',
                date('Y-m-d H:i:s') . ' ' . $e->getMessage() . "\n",
                FILE_APPEND
            );
        }
    }

    /**
     * Get logger interface from service
     *
     * @return LoggerInterface|null|false
     *
     * @throws Exception
     */
    protected function getLogger()
    {
        if (null === $this->logger) {
            $this->logger = $this->get('hhennes.modulesmanager.logger');
        }

        return $this->logger;
    }

    /**
     * Module configuration
     *
     * @return string
     *
     * @throws Exception
     */
    public function getContent(): string
    {
        $html = '';
        $configForm = new ConfigForm($this, $this->context);
        $html .= $configForm->postProcess();
        $html .= $configForm->renderForm();

        return $html;
    }
}
