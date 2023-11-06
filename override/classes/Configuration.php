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
class Configuration extends ConfigurationCore
{
    public static function updateValue($key, $values, $html = false, $idShopGroup = null, $idShop = null)
    {
        Hook::exec('actionConfigurationUpdateValue', [
            'configuration' => [
                'key' => $key, 'values' => $values, 'html' => $html, 'idShopGroup' => $idShopGroup, 'idShop' => $idShop,
            ],
        ]);

        return parent::updateValue($key, $values, $html, $idShopGroup, $idShop);
    }

    public static function deleteByName($key)
    {
        Hook::exec('actionConfigurationDeleteKey', ['key' => $key]);

        return parent::deleteByName($key);
    }

    public static function deleteFromContext($key, int $idShopGroup = null, int $idShop = null)
    {
        Hook::exec('actionConfigurationDeleteContextKey', ['key' => $key, 'idShop' => $idShop, 'idShopGroup' => $idShopGroup]);
        parent::deleteFromContext($key, $idShopGroup, $idShop);
    }
}
