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


class Translation implements UpgraderInterface
{
    use UpgraderResultTrait;

    /** @var string Upgrader type */
    public const TYPE = 'translation';

    /**
     * @param array $data
     */
    public function upgrade(array $data): void
    {
        if (!array_key_exists(self::TYPE, $data)) {
            return;
        }
        $data = $data[self::TYPE];

        //Add or update Configuration
        if (array_key_exists('update', $data)
            && is_array($data['update'])
            && count($data['update'])
        ) {
            foreach ($data['update'] as $key => $details) {

            }
        }
        //Delete configuration
        if (array_key_exists('delete', $data)
            && is_array($data['delete'])
            && count($data['delete'])
        ) {
            foreach ($data['delete'] as $key => $details) {

            }
        }
    }
}
