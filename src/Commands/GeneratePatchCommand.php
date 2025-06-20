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

use Exception;
use Hhennes\ModulesManager\Change;
use Hhennes\ModulesManager\Patch\Generator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * This command allow to generate an upgrade file (patch) from the command line
 */
class GeneratePatchCommand extends Command
{

    /**
     * @param Generator $patchGenerator
     * @param string|null $name
     */
    public function __construct(
        private readonly Generator $patchGenerator,
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
            ->setName('hhennes:module-manager:generate')
            ->setDescription('Generate a an upgrade file')
            ->addOption('entity', null, InputOption::VALUE_OPTIONAL)
            ->addOption('action', null, InputOption::VALUE_OPTIONAL)
            ->addOption('key', null, InputOption::VALUE_OPTIONAL)
            ->addOption('from_date', null, InputOption::VALUE_OPTIONAL)
            ->addOption('to_date', null, InputOption::VALUE_OPTIONAL);
    }

    /**
     * {@inheritdoc}
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $filters = [
                'entity' => $input->getOption('entity'),
                'action' => $input->getOption('action'),
                'key' => $input->getOption('key'),
                'from_date' => $input->getOption('from_date'),
                'to_date' => $input->getOption('to_date'),
            ];
            $changeIds = Change::getChangesByFilters($filters);
            if (count($changeIds)) {
                $upgradeFileName = $this->patchGenerator->generateChangeFile($changeIds, date('Ymd-His') . '-patch');
                $output->writeln(sprintf('<info>Upgrade file %s generated with success</info>', $upgradeFileName));
            } else {
                $output->writeln('<info>No changes found for update, no files was generated</info>');
            }
        } catch (Exception $e) {
            $output->writeln('<error>An error occurs when trying to generate the upgrade file</error>');
            $output->writeln(sprintf('<error>Exception error %s</error>', $e->getMessage()));

            return 1;
        }

        return 0;
    }
}
