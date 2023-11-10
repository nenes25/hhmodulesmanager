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
use Hhennes\ModulesManager\Patch\Manager;
use Module;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * This command is the main file of the module
 * It should be called during the CI/CD process to run the registered upgrades
 */
class ManageModulesCommand extends ContainerAwareCommand
{
    /** @var string Upgrade module name */
    protected string $moduleName = 'hhmodulesmanager';
    /** @var OutputInterface|null Sortie de la console */
    protected ?OutputInterface $output;
    /** @var array Errors of the process */
    protected array $errors = [];
    /** @var array Successes of the process */
    protected array $success = [];
    /** @var false|Module */
    private $module;

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

        try {
            $this->output = $output;
            $this->module = Module::getInstanceByName($this->moduleName);

            /** @var \Hhennes\ModulesManager\Patch\Manager $manager */
            $manager = $this->getContainer()->get('hhennes.modulesmanager.manager');

            $output->writeln('<info>Module Upgrade command launched</info>');
            $this->log('=========================');
            $this->log('Command Upgrade launched');

            $upgradeFiles = $manager->getUpgradeFiles();
            $appliedPatches = $manager->getAppliedPatches();
            foreach ($upgradeFiles as $upgradeFile) {
                $patchName = $upgradeFile->getBasename('.yml');
                if (!in_array($patchName, $appliedPatches)) {
                    $this->output->writeln('<comment>Applying patch "' . $patchName . '"</comment>');
                    $this->log('Applying patch ' . $patchName);
                    $manager->applyPatch($upgradeFile, $patchName);
                }
            }
            $this->logAndRenderResult($output, $manager);
            $output->writeln('<info>End of process</info>');
            $this->log('End of upgrade process');
        } catch (\Throwable $e) {
            $output->writeln(
                sprintf(
                    '<error>An error occurs when running the command %s</error>',
                    $e->getMessage()
                )
            );

            return 1;
        }

        return 0;
    }

    /**
     * Log and display the result of the run of the command
     *
     * @param OutputInterface $output
     * @param Manager $manager
     *
     * @return void
     */
    protected function logAndRenderResult(OutputInterface $output, Manager $manager): void
    {
        $this->success = array_merge($this->success, $manager->getSuccess());
        $this->errors = array_merge($this->errors, $manager->getErrors());
        if (count($this->success)) {
            $this->log('=== Success Messages');
            foreach ($this->success as $success) {
                if ($output->isVerbose()) {
                    $output->writeln('<fg=cyan> - ' . $success . '</>');
                }
                $this->log($success);
            }
        }
        if (count($this->errors)) {
            $this->log('=== Errors Messages');
            foreach ($this->errors as $error) {
                if ($output->isVerbose()) {
                    $output->writeln('<error> - ' . $error . '</error>');
                }
                $this->log($error);
            }
        }
    }

    /**
     * Log error messages
     *
     * @param string $message
     *
     * @return void
     */
    protected function log(string $message): void
    {
        $this->module->log($message);
    }
}
