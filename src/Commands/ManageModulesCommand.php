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

namespace Hhennes\ModulesManager\Commands;

use Configuration;
use Db;
use Hhennes\ModulesManager\Upgrader\Configuration as ConfigurationUpgrader;
use Hhennes\ModulesManager\Upgrader\Module as ModuleUpgrader;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Yaml\Parser;

/**
 * Commande de gestion de l'upgrade
 *
 * @Todo sortir toute la logique dans des autres classes
 */
class ManageModulesCommand extends ContainerAwareCommand
{
    /** @var string Nom du module d'upgrade */
    protected string $moduleName = 'hhmodulesmanager';
    /** @var OutputInterface|null Sortie de la console */
    protected ?OutputInterface $output;
    /** @var array Tableau des messages d'erreurs rencontrées */
    protected array $errors = [];
    /** @var array Tableau des actions réalisées avec succès */
    protected array $success = [];

    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this
            ->setName('hhennes:module-manager:manage')
            ->setDescription('Manage modules and configuration through cli');
    }

    /**
     * {@inheritdoc}
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        if (!Configuration::get(strtoupper($this->moduleName) . '_ENABLE_CLI_MODULES_UPDATE')) {
            $output->writeln('<info>Automatic module management is disabled</info>');

            return 0;
        }

        $this->output = $output;
        $appliedPatches = $this->getAppliedPatches();
        $output->writeln('<info>Module Upgrade command launched</info>');
        $this->_log('Command Upgrade launched');
        $upgradeFiles = $this->_getUpgradeFiles();
        foreach ($upgradeFiles as $upgradeFile) {
            $patchName = $upgradeFile->getBasename('.yml');
            if (!in_array($patchName, $appliedPatches)) {
                $this->output->writeln('<comment>Applying patch "' . $patchName . '"</comment>');
                $this->_log('Applying patch ' . $patchName);
                $data = $this->_parseUpgradeFile($upgradeFile);
                $this->_processUpgrade($data);
                $this->registerAppliedPatch($patchName);
            }
        }
        $this->_logAndRenderResult($output);
        $output->writeln('<info>End of process</info>');

        return 0;
    }

    /**
     * Lancement de l'upgrade
     *
     * @param array $data
     *
     * @return void
     */
    protected function _processUpgrade(array $data): void
    {
        if (array_key_exists('configuration', $data)) {
            $this->_processConfigurationUpgrade($data['configuration']);
        }
        if (array_key_exists('modules', $data)) {
            $this->_processModuleUpgrade($data['modules']);
        }
    }

    /**
     * Gestion de la configuration
     *
     * @param array $data
     *
     * @return void
     */
    protected function _processConfigurationUpgrade(array $data): void
    {
        $configurationUpgrader = new ConfigurationUpgrader();
        $configurationUpgrader->upgrade($data);
        $this->errors = array_merge($this->errors, $configurationUpgrader->getErrors());
        $this->success = array_merge($this->success, $configurationUpgrader->getSuccess());
    }

    /**
     * Gestion des modules
     *
     * @param array $data
     *
     * @return void
     */
    protected function _processModuleUpgrade(array $data): void
    {
        $moduleUpgrader = new ModuleUpgrader();
        $moduleUpgrader->upgrade($data);
        $this->errors = array_merge($this->errors, $moduleUpgrader->getErrors());
        $this->success = array_merge($this->success, $moduleUpgrader->getSuccess());
    }

    /**
     * Log et Affichage du résultat de la commande
     *
     * @param OutputInterface $output
     *
     * @return void
     */
    protected function _logAndRenderResult(OutputInterface $output): void
    {
        if (count($this->success)) {
            $this->_log('=== Success Messages');
            foreach ($this->success as $success) {
                if ($output->isVerbose()) {
                    $output->writeln('<fg=cyan> - ' . $success . '</>');
                }
                $this->_log($success);
            }
        }
        if (count($this->errors)) {
            $this->_log('=== Errors Messages');
            foreach ($this->errors as $error) {
                if ($output->isVerbose()) {
                    $output->writeln('<error> - ' . $error . '</error>');
                }
                $this->_log($error);
            }
        }
    }

    /**
     * Récupération des fichiers de maj
     *
     * @return Finder
     */
    protected function _getUpgradeFiles()
    {
        $finder = new Finder();

        return $finder->files()
            ->in($this->_getUpgradeDirectory())
            ->sortByName()
            ->name('*.yml');
    }

    /**
     * Récupération du dossier qui contient les mises à jour
     *
     * @return string
     */
    protected function _getUpgradeDirectory(): string
    {
        return _PS_MODULE_DIR_ . $this->moduleName . '/upgrades';
    }

    /**
     * Analyse et traitement du fichier d'upgrade
     *
     * @param \Symfony\Component\Finder\SplFileInfo $file
     *
     * @return array|mixed|\stdClass|\Symfony\Component\Yaml\Tag\TaggedValue|null
     */
    protected function _parseUpgradeFile(\Symfony\Component\Finder\SplFileInfo $file)
    {
        try {
            $parser = new Parser();
            $data = $parser->parseFile($file->getPathname());

            return $data;
        } catch (\Exception $e) {
            $this->errors[] = 'Unable to parse configuration';
            $this->output->writeln('<error>Unable to parse configuration</error>');

            return [];
        }
    }

    /**
     * Récupération de la liste des patchs déjà appliqués
     *
     * @return array
     */
    protected function getAppliedPatches(): array
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
     * Insertion d'un patch
     *
     * @param string $patchName
     *
     * @return void
     */
    protected function registerAppliedPatch(string $patchName): void
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
     * Log error messages
     *
     * @param string $message
     *
     * @return void
     *
     * @todo mettre les logs dans le dossier des logs par défaut
     */
    protected function _log(string $message): void
    {
        file_put_contents(
            _PS_MODULE_DIR_ . $this->moduleName . '/logs/' . date('Y-m-d') . '.log',
            date('Y-m-d H:i:s') . ' - ' . $message . "\n",
            FILE_APPEND
        );
    }
}
