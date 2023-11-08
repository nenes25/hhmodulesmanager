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

class HhModulesManager extends Module
{
    /**
     * Liste des configurations à ignorer
     */
    public const EXCLUDED_CONFIGURATIONS = [
        'HHMODULESMANAGER_ENABLE_CHANGE_RECORDER',
    ];

    public function __construct()
    {
        $this->name = 'hhmodulesmanager';
        $this->tab = 'administration';
        $this->version = '0.3.0';
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
     * Exécuté après l'installation d'un module
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
     * Hook exécuté après la désinstallation d'un module
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
     * Hook (custom) pour gérer les mises à jour de modules
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
     * Hook (custom) exécuté AVANT la mise à jour d'une configuration
     *
     * On peut donc détecter si la configuration a changé ou non
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
     * Hook (custom) exécuté AVANT la suppression d'une configuration
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
     * Hook (custom) exécuté AVANT la suppression d'une configuration dans un contexte spécifique
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
     * Défini si l'enregistrement des actions est actif
     *
     * @return bool
     */
    public function isRecorderEnabled(): bool
    {
        return (bool) Configuration::get(strtoupper($this->name) . '_ENABLE_CHANGE_RECORDER');
    }

    /**
     * Défini si la cla est exclu des changements de conf
     *
     * @param string $key
     *
     * @return bool
     */
    protected function isExludedConfiguration($key): bool
    {
        return in_array($key, self::EXCLUDED_CONFIGURATIONS);
    }

    /**
     * Historisation des événements
     *
     * @param string $name Nom de l'événement
     * @param string $type Type de l'événement
     * @param string|null $key Clé de l'événement
     * @param array $details Tableau de détails
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

            file_put_contents(
                dirname(__FILE__) . '/logs/eventlogs.log',
                $message,
                FILE_APPEND
            );
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

    /**
     * @Todo use monolog
     *
     * @param string $message
     * @param int $level
     * @return void
     */
    public function log(string $message, int $level = 0):void
    {
        file_put_contents(
            dirname(__FILE__) . '/logs/'.date('Y-m-d').'.log',
            date('Y-m-d H:i:s').' '.$message."\n",
            FILE_APPEND
        );
    }

    /**
     * Configuration du module
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
