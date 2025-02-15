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

use Hhennes\ModulesManager\DbUpgrader\Upgrader;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Logger\ConsoleLogger;
use \Module;

class ApplyPrestashopDbUpgrade extends ContainerAwareCommand
{

    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this
            ->setName('hhennes:module-manager:upgrade-db-version')
            ->setDescription('Apply db upgrade from current version to last version')
            ->addArgument('from-version', InputArgument::REQUIRED, 'Version from where the db upgrade should start')
            ->addArgument('to-version', InputArgument::REQUIRED, 'Last where the db upgrade should stop')
            ->setHelp(
                'This command allow to run db upgrade of the module autoupgrade whithout running it directly' . PHP_EOL
                . 'Thus it allows to push the code through CI/CD and to run this command to finish the upgrade after the push' . PHP_EOL
            );
    }

    /**
     * {@inheritdoc}
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        if (!Module::getInstanceByName('autoupgrade') || !Module::isInstalled('autoupgrade')) {
            $output->writeln(
                '<error>The module autoupgrade is required and should be installed to use this tool</error>'
            );
            return 1;
        }
        $fromVersion = $input->getArgument('from-version');
        $toVersion = $input->getArgument('to-version');

        if (!$this->isvalidPsVersion($fromVersion) || !$this->isvalidPsVersion($toVersion)) {
            $output->writeln('<error>Please enter valid from and to versions</error>');
        }
        $output->writeln('<info>Upgrade process start</info>');
        $logger = new ConsoleLogger($output);
        $dbUpgrader = new Upgrader($logger);
        try {
            $dbUpgrader->upgradeDb($fromVersion, $toVersion);
            $output->writeln(sprintf('<info>Db version %s applied with success</info>', $toVersion));
        } catch (\Exception $e) {
            $output->writeln('<error>Unable to apply upgrade, please check logs</error>');
            $logger->error('Error : ' . $e->getMessage());
            return 1;
        }
        return 0;
    }

    /**
     * Check if ps version is Valid
     *
     * Only from the one where the console is available
     *
     * @param string $psVersion
     * @return bool
     */
    protected function isvalidPsVersion(string $psVersion): bool
    {
        return preg_match('#^(1\.7.[5-8].[0-9]{1,2}|8.[0-1].[0-9])#',$psVersion);
    }

}