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

namespace Hhennes\ModulesManager\Converter;

use Exception;
use Hhennes\ModulesManager\Change;

class Module implements ConverterInterface
{
    /** @var string Type d'upgrade */
    public const TYPE = 'modules';

    /**
     * Liste des actions autorisées
     */
    public const ALLOWED_ACTIONS = [
        'install',
        'uninstall',
        'enable',
        'disable',
        'update',
    ];

    /**
     * {@inheritDoc}
     */
    public function canConvert(Change $change): bool
    {
        return $change->entity == 'module'; //@todo Harmoniser ici le code "modules" et "module"
    }

    /**
     * {@inheritDoc}
     */
    public function convert(Change $change, array &$currentChangesArray): void
    {
        //Initialisation du premier niveau
        if (!array_key_exists(self::TYPE, $currentChangesArray)) {
            $currentChangesArray[self::TYPE] = [];
        }
        if (!in_array($change->action, self::ALLOWED_ACTIONS)) {
            throw new Exception(
                'Unknow configuration action , allowed values : '
                . implode(',', self::ALLOWED_ACTIONS));
        }
        if (!array_key_exists($change->action, $currentChangesArray[self::TYPE])) {
            $currentChangesArray[self::TYPE][$change->action] = [];
        }
        if (!in_array($change->key, $currentChangesArray[self::TYPE][$change->action])) {
            $currentChangesArray[self::TYPE][$change->action][] = $change->key;
        }
    }
}
