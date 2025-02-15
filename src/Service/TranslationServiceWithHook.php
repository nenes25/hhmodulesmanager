<?php
/**
 * Hervé HENNES
 *
 * NOTICE OF LICENSE
 *
 * This file is licenced under the Software License Agreement.
 * With the purchase or the installation of the software in your application
 * you accept the licence agreement
 *
 * @author    Hervé HENNES <contact@h-hennes.fr>
 * @copyright since 2025 Hervé HENNES
 * @license   Commercial license (You can not resell or redistribute this software.)
 *
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
                'theme' => $theme
            ];
            $this->hookDispatcher->dispatchWithParameters('actionTranslationSave',['translation' => $translation]);
        }
    }
}