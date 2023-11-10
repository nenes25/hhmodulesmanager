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

use Hhennes\ModulesManager\Change;

interface ConverterInterface
{
    /**
     * Conversion du changement sous forme d'un tableau qui s'ajoute au tableau des modifications
     *
     * @param Change $change
     * @param array $currentChangesArray
     *
     * @throws \Exception
     */
    public function convert(Change $change, array &$currentChangesArray): void;

    /**
     * Define if the converter can convert the current change
     *
     * @param Change $change
     *
     * @return bool
     */
    public function canConvert(Change $change): bool;
}
