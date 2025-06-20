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

use PrestaShop\PrestaShop\Core\Module\ModuleRepositoryInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ListUpgradableModulesCommand extends Command
{
    public function __construct(
        private readonly ModuleRepositoryInterface $moduleRepository,
        ?string $name = null
    )
    {
        parent::__construct($name);
    }

    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this
            ->setName('hhennes:module-manager:list-upgradable-modules')
            ->setDescription('List all the modules which can be upgraded')
            ->addOption('ignore', null, InputOption::VALUE_OPTIONAL);
    }

    /**
     * {@inheritdoc}
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $modules = $this->getUpgradableModules();
        if (count($modules)) {
            asort($modules);
            $output->writeln(
                sprintf("<info>The following modules can be upgraded :\n%s </info>", implode("\n", $modules))
            );
        } else {
            $output->writeln('<info>No module to upgrade founds</info>');
        }

        return 0;
    }

    /**
     * Get the List of the names of the modules that can be upgraded
     *
     * @return array List of the names of the modules that can be upgraded
     */
    protected function getUpgradableModules(): array
    {
        $modulesNames = [];
        $installedModules = $this->moduleRepository->getInstalledModules();
        foreach ($installedModules as $installedModule) {
            if ($installedModule->canBeUpgraded()) {
                $modulesNames[] = $installedModule->get('name');
            }
        }

        return $modulesNames;
    }
}
