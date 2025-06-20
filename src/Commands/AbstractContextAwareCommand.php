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

use Employee;
use PrestaShop\PrestaShop\Adapter\LegacyContext;
use PrestaShop\PrestaShop\Core\ConfigurationInterface;
use PrestaShop\PrestaShop\Core\Context\ContextBuilderPreparer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Base command that initializes the PrestaShop context required for CLI execution.
 *
 * In a web request, the Language context and the Employee are set by event listeners.
 * In CLI they are never initialized, which causes failures in services that depend on them
 * (e.g. ModuleRepository uses LanguageContext for its cache key).
 */
abstract class AbstractContextAwareCommand extends Command
{
    public function __construct(
        protected readonly LegacyContext $legacyContext,
        protected readonly ContextBuilderPreparer $contextBuilderPreparer,
        protected readonly ConfigurationInterface $configuration,
    ) {
        parent::__construct();
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        // Following the same pattern as PrestaShopBundle\Command\ModuleCommand::init():
        // we need an employee in the context or module hooks won't work (see LegacyHookSubscriber).
        // Even a non-existing employee is fine according to PS core.
        if (!$this->legacyContext->getContext()->employee) {
            $this->legacyContext->getContext()->employee = new \Employee(42);
        }

        // ModuleRepository depends on LanguageContext for its cache key, so we must
        // initialize it before any module-related service is used.
        $this->contextBuilderPreparer->prepareLanguageId(
            (int) $this->configuration->get('PS_LANG_DEFAULT')
        );
    }
}
