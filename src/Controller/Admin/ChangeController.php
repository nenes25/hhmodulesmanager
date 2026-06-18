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

namespace Hhennes\ModulesManager\Controller\Admin;

use Hhennes\ModulesManager\Change;
use Hhennes\ModulesManager\Filter\ChangeFilters;
use Hhennes\ModulesManager\Form\ChangeType;
use Hhennes\ModulesManager\Grid\Definition\Factory\ChangeGridDefinitionFactory;
use Hhennes\ModulesManager\Patch\Generator;
use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ChangeController extends FrameworkBundleAdminController
{
    public function listAction(ChangeFilters $filters): Response
    {
        $gridFactory = $this->get('hhennes.modulesmanager.grid.change.grid_factory');
        $grid = $gridFactory->getGrid($filters);
        $isRecorderEnabled = (bool) \Configuration::get('HHMODULESMANAGER_ENABLE_CHANGE_RECORDER');

        return $this->render(
            '@Modules/hhmodulesmanager/views/templates/admin/change/list.html.twig',
            [
                'grid' => $this->presentGrid($grid),
                'display_warning' => !$isRecorderEnabled,
            ]
        );
    }

    public function searchAction(Request $request): RedirectResponse
    {
        $responseBuilder = $this->get('prestashop.bundle.grid.response_builder');

        return $responseBuilder->buildSearchResponse(
            $this->get('hhennes.modulesmanager.grid.definition.factory.change'),
            $request,
            ChangeGridDefinitionFactory::GRID_ID,
            'admin_hhmodulesmanager_change_list'
        );
    }

    public function createAction(Request $request): Response
    {
        $form = $this->createForm(ChangeType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $change = new Change();
            $change->entity = $data['entity'];
            $change->action = $data['action'];
            $change->key = $data['key'];
            $change->details = $data['details'];
            $change->add();

            $this->addFlash('success', $this->trans('Successful creation', 'Admin.Notifications.Success'));

            return $this->redirectToRoute('admin_hhmodulesmanager_change_list');
        }

        return $this->render(
            '@Modules/hhmodulesmanager/views/templates/admin/change/form.html.twig',
            ['form' => $form->createView()]
        );
    }

    public function editAction(int $changeId, Request $request): Response
    {
        $change = new Change($changeId);
        if (!$change->id) {
            return $this->redirectToRoute('admin_hhmodulesmanager_change_list');
        }

        $form = $this->createForm(ChangeType::class, [
            'entity' => $change->entity,
            'action' => $change->action,
            'key' => $change->key,
            'details' => $change->details,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $change->entity = $data['entity'];
            $change->action = $data['action'];
            $change->key = $data['key'];
            $change->details = $data['details'];
            $change->update();

            $this->addFlash('success', $this->trans('Successful update', 'Admin.Notifications.Success'));

            return $this->redirectToRoute('admin_hhmodulesmanager_change_list');
        }

        return $this->render(
            '@Modules/hhmodulesmanager/views/templates/admin/change/form.html.twig',
            ['form' => $form->createView(), 'change' => $change]
        );
    }

    public function deleteAction(int $changeId): RedirectResponse
    {
        $change = new Change($changeId);
        if ($change->id) {
            $change->delete();
            $this->addFlash('success', $this->trans('Successful deletion', 'Admin.Notifications.Success'));
        }

        return $this->redirectToRoute('admin_hhmodulesmanager_change_list');
    }

    public function bulkDeleteAction(Request $request): RedirectResponse
    {
        $ids = $request->request->all(ChangeGridDefinitionFactory::GRID_ID . '_bulk');

        foreach ($ids as $id) {
            $change = new Change((int) $id);
            if ($change->id) {
                $change->delete();
            }
        }

        $this->addFlash('success', $this->trans('Successful deletion', 'Admin.Notifications.Success'));

        return $this->redirectToRoute('admin_hhmodulesmanager_change_list');
    }

    public function generateAction(int $changeId): RedirectResponse
    {
        try {
            /** @var Generator $patchGenerator */
            $patchGenerator = $this->get('hhennes.modulesmanager.patch.generator');
            $patchGenerator->generateChangeFile([$changeId], date('Ymd-His') . '-patch');
            $this->addFlash(
                'success',
                $this->trans('Update file generated successfully in upgrade directory', 'Modules.Hhmodulesmanager.Admin')
            );
        } catch (\Exception $e) {
            $this->addFlash(
                'error',
                $this->trans('An error occurred while generating the patch file', 'Modules.Hhmodulesmanager.Admin')
            );
        }

        return $this->redirectToRoute('admin_hhmodulesmanager_change_list');
    }

    public function bulkGenerateAction(Request $request): RedirectResponse
    {
        $ids = $request->request->all(ChangeGridDefinitionFactory::GRID_ID . '_bulk');

        try {
            /** @var Generator $patchGenerator */
            $patchGenerator = $this->get('hhennes.modulesmanager.patch.generator');
            $patchGenerator->generateChangeFile($ids, date('Ymd-His') . '-patch');
            $this->addFlash(
                'success',
                $this->trans('Update file generated successfully in upgrade directory', 'Modules.Hhmodulesmanager.Admin')
            );
        } catch (\Exception $e) {
            $this->addFlash(
                'error',
                $this->trans('An error occurred while generating the patch file', 'Modules.Hhmodulesmanager.Admin')
            );
        }

        return $this->redirectToRoute('admin_hhmodulesmanager_change_list');
    }
}
