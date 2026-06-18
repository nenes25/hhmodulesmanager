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

namespace Hhennes\ModulesManager\Grid\Definition\Factory;

use PrestaShop\PrestaShop\Core\ConfigurationInterface;
use PrestaShop\PrestaShop\Core\Grid\Action\Bulk\BulkActionCollection;
use PrestaShop\PrestaShop\Core\Grid\Action\Bulk\Type\SubmitBulkAction;
use PrestaShop\PrestaShop\Core\Grid\Action\Row\RowActionCollection;
use PrestaShop\PrestaShop\Core\Grid\Action\Row\Type\LinkRowAction;
use PrestaShop\PrestaShop\Core\Grid\Action\Row\Type\SubmitRowAction;
use PrestaShop\PrestaShop\Core\Grid\Column\ColumnCollection;
use PrestaShop\PrestaShop\Core\Grid\Column\Type\Common\ActionColumn;
use PrestaShop\PrestaShop\Core\Grid\Column\Type\Common\BulkActionColumn;
use PrestaShop\PrestaShop\Core\Grid\Column\Type\Common\DataColumn;
use PrestaShop\PrestaShop\Core\Grid\Column\Type\Common\DateTimeColumn;
use PrestaShop\PrestaShop\Core\Grid\Definition\Factory\AbstractGridDefinitionFactory;
use PrestaShop\PrestaShop\Core\Grid\Definition\Factory\BulkDeleteActionTrait;
use PrestaShop\PrestaShop\Core\Grid\Definition\Factory\DeleteActionTrait;
use PrestaShop\PrestaShop\Core\Grid\Filter\Filter;
use PrestaShop\PrestaShop\Core\Grid\Filter\FilterCollection;
use PrestaShop\PrestaShop\Core\Hook\HookDispatcherInterface;
use PrestaShopBundle\Form\Admin\Type\SearchAndResetType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class ChangeGridDefinitionFactory extends AbstractGridDefinitionFactory
{
    use DeleteActionTrait;
    use BulkDeleteActionTrait;

    public const GRID_ID = 'hhmodulesmanager_change';

    private ConfigurationInterface $configuration;

    public function __construct(HookDispatcherInterface $hookDispatcher, ConfigurationInterface $configuration)
    {
        parent::__construct($hookDispatcher);
        $this->configuration = $configuration;
    }

    protected function getId(): string
    {
        return self::GRID_ID;
    }

    protected function getName(): string
    {
        return $this->trans('Module Manager Changes', [], 'Modules.Hhmodulesmanager.Admin');
    }

    protected function getColumns(): ColumnCollection
    {
        return (new ColumnCollection())
            ->add(
                (new BulkActionColumn('bulk'))
                    ->setOptions(['bulk_field' => 'id_change'])
            )
            ->add(
                (new DataColumn('id_change'))
                    ->setName($this->trans('ID', [], 'Admin.Global'))
                    ->setOptions(['field' => 'id_change'])
            )
            ->add(
                (new DataColumn('entity'))
                    ->setName($this->trans('Entity', [], 'Admin.Global'))
                    ->setOptions(['field' => 'entity'])
            )
            ->add(
                (new DataColumn('action'))
                    ->setName($this->trans('Action', [], 'Admin.Actions'))
                    ->setOptions(['field' => 'action'])
            )
            ->add(
                (new DataColumn('key'))
                    ->setName($this->trans('Key', [], 'Admin.Global'))
                    ->setOptions(['field' => 'key'])
            )
            ->add(
                (new DataColumn('details'))
                    ->setName($this->trans('Details', [], 'Admin.Global'))
                    ->setOptions(['field' => 'details'])
            )
            ->add(
                (new DateTimeColumn('date_add'))
                    ->setName($this->trans('Date', [], 'Admin.Global'))
                    ->setOptions([
                        'field' => 'date_add',
                        'format' => 'Y-m-d H:i:s',
                    ])
            )
            ->add(
                (new ActionColumn('actions'))
                    ->setName($this->trans('Actions', [], 'Admin.Global'))
                    ->setOptions([
                        'actions' => (new RowActionCollection())
                            ->add(
                                (new LinkRowAction('edit'))
                                    ->setName($this->trans('Edit', [], 'Admin.Actions'))
                                    ->setIcon('edit')
                                    ->setOptions([
                                        'route' => 'admin_hhmodulesmanager_change_edit',
                                        'route_param_name' => 'changeId',
                                        'route_param_field' => 'id_change',
                                    ])
                            )
                            ->add(
                                (new SubmitRowAction('generate'))
                                    ->setName($this->trans('Generate patch', [], 'Modules.Hhmodulesmanager.Admin'))
                                    ->setIcon('file_download')
                                    ->setOptions([
                                        'method' => 'POST',
                                        'route' => 'admin_hhmodulesmanager_change_generate',
                                        'route_param_name' => 'changeId',
                                        'route_param_field' => 'id_change',
                                    ])
                            )
                            ->add(
                                $this->buildDeleteAction(
                                    'admin_hhmodulesmanager_change_delete',
                                    'changeId',
                                    'id_change'
                                )
                            ),
                    ])
            );
    }

    protected function getFilters(): FilterCollection
    {
        return (new FilterCollection())
            ->add(
                (new Filter('id_change', TextType::class))
                    ->setTypeOptions([
                        'required' => false,
                        'attr' => ['placeholder' => $this->trans('Search ID', [], 'Admin.Actions')],
                    ])
                    ->setAssociatedColumn('id_change')
            )
            ->add(
                (new Filter('entity', TextType::class))
                    ->setTypeOptions([
                        'required' => false,
                        'attr' => ['placeholder' => $this->trans('Search entity', [], 'Admin.Actions')],
                    ])
                    ->setAssociatedColumn('entity')
            )
            ->add(
                (new Filter('action', TextType::class))
                    ->setTypeOptions([
                        'required' => false,
                        'attr' => ['placeholder' => $this->trans('Search action', [], 'Admin.Actions')],
                    ])
                    ->setAssociatedColumn('action')
            )
            ->add(
                (new Filter('key', TextType::class))
                    ->setTypeOptions([
                        'required' => false,
                        'attr' => ['placeholder' => $this->trans('Search key', [], 'Admin.Actions')],
                    ])
                    ->setAssociatedColumn('key')
            )
            ->add(
                (new Filter('actions', SearchAndResetType::class))
                    ->setTypeOptions([
                        'reset_route' => 'admin_common_reset_search_by_filter_id',
                        'reset_route_params' => ['filterId' => self::GRID_ID],
                        'redirect_route' => 'admin_hhmodulesmanager_change_list',
                    ])
                    ->setAssociatedColumn('actions')
            );
    }

    protected function getBulkActions(): BulkActionCollection
    {
        if (!$this->configuration->get('HHMODULESMANAGER_ENABLE_CHANGE_RECORDER')) {
            return new BulkActionCollection();
        }

        return (new BulkActionCollection())
            ->add(
                (new SubmitBulkAction('bulk_generate'))
                    ->setName($this->trans('Generate patch with selected', [], 'Modules.Hhmodulesmanager.Admin'))
                    ->setOptions([
                        'submit_route' => 'admin_hhmodulesmanager_change_bulk_generate',
                        'confirm_message' => $this->trans(
                            'Generate a patch with selected items?',
                            [],
                            'Admin.Notifications.Warning'
                        ),
                    ])
            )
            ->add(
                $this->buildBulkDeleteAction('admin_hhmodulesmanager_change_bulk_delete')
            );
    }
}
