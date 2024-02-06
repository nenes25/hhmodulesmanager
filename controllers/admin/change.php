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
include _PS_MODULE_DIR_ . 'hhmodulesmanager/vendor/autoload.php';

use Hhennes\ModulesManager\Change;

class changeController extends ModuleAdminController
{
    /** @var \HhModulesManager */
    public $module;

    public $_error = [];

    public function __construct()
    {
        $this->bootstrap = true;
        $this->table = 'hhmodulesmanager_change';
        $this->identifier = 'id_change';
        $this->className = Change::class;
        $this->lang = false;
        $this->context = Context::getContext();
        $this->addRowAction('edit');
        $this->addRowAction('delete');
        $this->addRowAction('generateUpdate');

        parent::__construct();

        $this->_conf[99] = $this->l('Update file generated with success in upgrade directory');
        $this->_error[99] = $this->l('An error occurs when generating the file');

        if ($this->module->isRecorderEnabled()) {
            $this->bulk_actions = [
                'delete' => [
                    'text' => $this->l('Delete selected'),
                    'confirm' => $this->l('Delete selected items?'),
                    'icon' => 'icon-trash',
                ],
                'generate' => [
                    'text' => $this->l('Generate a new update'),
                    'confirm' => $this->l('Generate a new update with selected changes?'),
                    'icon' => 'icon-folder-close',
                ],
            ];
        }

        $this->fields_list = ['entity' => [
            'title' => $this->l('entity'),
            'lang' => false,
        ],
            'action' => [
                'title' => $this->l('action'),
                'lang' => false,
            ],
            'key' => [
                'title' => $this->l('key'),
                'lang' => false,
            ],
            'details' => [
                'title' => $this->l('details'),
                'lang' => false,
            ],
            'date_add' => [
                'title' => $this->l('date_add'),
                'lang' => false,
            ],
            'date_upd' => [
                'title' => $this->l('date_upd'),
                'lang' => false,
            ],
        ];
    }

    /**
     * Get variables for the display of the list
     *
     * @return array
     */
    public function getTemplateListVars()
    {
        //Affichage d'un message d'info dans le cas ou l'enregistrement n'est pas activé
        if (!$this->module->isRecorderEnabled()) {
            return [
               'display_warning' => true,
           ];
        }

        return $this->tpl_list_vars;
    }

    /**
     * Display Object Form
     *
     * @return string
     *
     * @throws SmartyException
     */
    public function renderForm()
    {
        $this->fields_form = [
            'legend' => [
                'title' => $this->l('Edit Change'),
                'icon' => 'icon-cog',
            ],
            'input' => [
                [
                    'type' => 'text',
                    'label' => $this->l('entity'),
                    'name' => 'entity',
                    'lang' => false,
                    'required' => false,
                ],

                [
                    'type' => 'text',
                    'label' => $this->l('action'),
                    'name' => 'action',
                    'lang' => false,
                    'required' => false,
                ],

                [
                    'type' => 'text',
                    'label' => $this->l('key'),
                    'name' => 'key',
                    'lang' => false,
                    'required' => false,
                ],

                [
                    'type' => 'text',
                    'label' => $this->l('details'),
                    'name' => 'details',
                    'lang' => false,
                    'required' => false,
                ],

                [
                    'type' => 'text',
                    'label' => $this->l('date_add'),
                    'name' => 'date_add',
                    'lang' => false,
                    'required' => false,
                ],

                [
                    'type' => 'text',
                    'label' => $this->l('date_upd'),
                    'name' => 'date_upd',
                    'lang' => false,
                    'required' => false,
                ],
            ],
            'submit' => [
                'title' => $this->l('Save'),
            ],
        ];

        return parent::renderForm();
    }

    /**
     * Add button in Toolbar
     *
     * @return void
     */
    public function initPageHeaderToolbar()
    {
        $this->page_header_toolbar_btn['new_object'] = [
            'href' => self::$currentIndex . '&addhhmodulesmanager_change&token=' . $this->token,
            'desc' => $this->l('Add new change'),
            'icon' => 'process-icon-new',
        ];
        parent::initPageHeaderToolbar();
    }

    /**
     * Display a new action to generate a single update for a row
     *
     * @param string $token
     * @param int $id
     * @param string|null $name
     *
     * @return string
     *
     * @throws SmartyException
     */
    public function displaygenerateUpdateLink($token, $id, $name = null): string
    {
        $this->context->smarty->assign([
            'href' => self::$currentIndex .
                '&' . $this->identifier . '=' . $id .
                '&action=generateUpdate&token=' . ($token != null ? $token : $this->token),
            'action' => $this->l('Generate a new update'),
        ]);

        return $this->context->smarty->fetch(
            _PS_MODULE_DIR_ . $this->module->name . '/views/templates/admin/change/helpers/list/list_action_update.tpl'
        );
    }

    /**
     * Generate a single update file
     *
     * @return void
     */
    public function processGenerateUpdate()
    {
        if ($id_change = Tools::getValue('id_change')) {
            try {
                /** @var \Hhennes\ModulesManager\Patch\Generator $patchGenerator */
                $patchGenerator = $this->get('hhennes.modulesmanager.patch.generator');
                $patchGenerator->generateChangeFile([$id_change], date('Ymd-His') . '-patch');
                $this->setRedirectAfter(self::$currentIndex . '&token=' . $this->token . '&conf=99');
            } catch (Exception $e) {
                $this->module->log(
                    'ERROR - ' . __METHOD__ . '- Unable to generate patch , get error ' . $e->getMessage()
                );
                $this->setRedirectAfter(self::$currentIndex . '&token=' . $this->token . '&error=99');
            }
        } else {
            $this->setRedirectAfter(self::$currentIndex . '&token=' . $this->token . '&error=99');
        }
    }

    /**
     * Generate the upgrade file with bulk processes
     *
     * @return void
     */
    public function processBulkGenerate()
    {
        try {
            $changeIds = Tools::getValue('hhmodulesmanager_changeBox');
            /** @var \Hhennes\ModulesManager\Patch\Generator $patchGenerator */
            $patchGenerator = $this->get('hhennes.modulesmanager.patch.generator');
            $patchGenerator->generateChangeFile($changeIds, date('Ymd-His') . '-patch');
            $this->setRedirectAfter(self::$currentIndex . '&token=' . $this->token . '&conf=99');
        } catch (Exception $e) {
            $this->module->log(
                'ERROR - ' . __METHOD__ . '- Unable to generate patch , get error ' . $e->getMessage()
            );
            $this->setRedirectAfter(self::$currentIndex . '&token=' . $this->token . '&error=99');
        }
    }

    /**
     * Translation Override
     *
     * @param string $string
     * @param string $class
     * @param bool $addslashes
     * @param bool $htmlentities
     *
     * @return string
     */
    protected function l($string, $class = null, $addslashes = false, $htmlentities = true)
    {
        return $this->module->l($string, 'change');
    }
}
