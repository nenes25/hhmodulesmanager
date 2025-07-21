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

namespace Hhennes\ModulesManager\Service;

use PrestaShop\PrestaShop\Core\Hook\HookDispatcher;
use Symfony\Component\String\Slugger\AsciiSlugger;

class TranslationServiceWithHook extends \PrestaShopBundle\Service\TranslationService
{
    protected $hookDispatcher;

    public function __construct(HookDispatcher $hookDispatcher)
    {
        $this->hookDispatcher = $hookDispatcher;
    }

    public function saveTranslationMessage($lang, $domain, $key, $translationValue, $theme = null)
    {
        $success = parent::saveTranslationMessage($lang, $domain, $key, $translationValue, $theme);
        if ($success) {
            $translation = [
                'slug' => (new AsciiSlugger())->slug($key),
                'id_lang' => $lang->getId(),
                'domain' => $domain,
                'key' => $key,
                'translationValue' => $translationValue,
                'theme' => $theme,
            ];
            $this->hookDispatcher->dispatchWithParameters('actionTranslationSave', ['translation' => $translation]);
        }
    }
}
