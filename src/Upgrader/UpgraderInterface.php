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

namespace Hhennes\ModulesManager\Upgrader;

interface UpgraderInterface
{
    /**
     * Lancement de la mise à jour
     *
     * @param array $data
     *
     * @return void
     */
    public function upgrade(array $data): void;

    /**
     * Récupération de la liste des actions effectuées avec succès
     *
     * @return array Liste des actions effectuées avec succès
     */
    public function getSuccess(): array;

    /**
     * Récupération de la liste des erreurs rencontrées
     *
     * @return array Liste des erreurs rencontrées
     */
    public function getErrors(): array;
}
