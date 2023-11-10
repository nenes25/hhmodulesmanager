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

class Configuration implements ConverterInterface
{
    /** @var string Converter type */
    public const TYPE = 'configuration';

    /**
     * @var array Allowed actions
     */
    public const ALLOWED_ACTIONS = [
        self::KEY_ADD_OR_UPDATE,
        self::KEY_DELETE,
    ];

    /** @var string Add or update a configuration */
    public const KEY_ADD_OR_UPDATE = 'add_or_update';

    /** @var string Delete a configuration */
    public const KEY_DELETE = 'delete';

    /**
     * {@inheritDoc}
     */
    public function canConvert(Change $change): bool
    {
        return $change->entity == self::TYPE;
    }

    /**
     * {@inheritDoc}
     */
    public function convert(Change $change, array &$currentChangesArray): void
    {
        if (!array_key_exists(self::TYPE, $currentChangesArray)) {
            $currentChangesArray[self::TYPE] = [];
        }

        switch ($change->action) {
            case 'add':
            case 'update':
                $key = self::KEY_ADD_OR_UPDATE;
                break;
            case 'delete':
                $key = self::KEY_DELETE;
                break;
            default:
                throw new Exception('Unknow configuration action , allowed values : ' . implode(',', self::ALLOWED_ACTIONS));
        }
        if (!array_key_exists($key, $currentChangesArray[self::TYPE])) {
            $currentChangesArray[self::TYPE][$key] = [];
        }

        $changesDetails = json_decode($change->details, true);
        $currentChangesArray[self::TYPE][$key][$change->key] = $changesDetails['configuration']['values'];
    }
}
