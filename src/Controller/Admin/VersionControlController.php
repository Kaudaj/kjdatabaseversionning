<?php
/**
 * Copyright since 2019 Kaudaj
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to info@kaudaj.com so we can send you a copy immediately.
 *
 * @author    Kaudaj <info@kaudaj.com>
 * @copyright Since 2019 Kaudaj
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 */

declare(strict_types=1);

namespace Kaudaj\Module\DBVCS\Controller\Admin;

use Kaudaj\Module\DBVCS\Search\Filters\ChangeFilters;
use Kaudaj\Module\DBVCS\Search\Filters\CommitFilters;
use PrestaShop\PrestaShop\Core\Grid\GridFactory;
use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;
use PrestaShopBundle\Security\Annotation\AdminSecurity;
use PrestaShopBundle\Security\Annotation\ModuleActivated;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class VersionControlController
 *
 * @ModuleActivated(moduleName="kjdbvcs", redirectRoute="admin_module_manage")
 */
class VersionControlController extends FrameworkBundleAdminController
{
    /**
     * @AdminSecurity(
     *     "is_granted(['read'], request.get('_legacy_controller'))",
     *     message="You do not have permission to access this."
     * )
     *
     * @param Request $request
     * @param ChangeFilters<string, mixed> $changeFilters
     * @param CommitFilters<string, mixed> $commitFilters
     *
     * @return Response
     */
    public function indexAction(Request $request, ChangeFilters $changeFilters, CommitFilters $commitFilters): Response
    {
        /** @var GridFactory */
        $changesGridFactory = $this->get('kaudaj.module.dbvcs.grid.change_grid_factory');
        $changesGrid = $changesGridFactory->getGrid($changeFilters);

        /** @var GridFactory */
        $commitsGridFactory = $this->get('kaudaj.module.dbvcs.grid.commit_grid_factory');
        $commitsGrid = $commitsGridFactory->getGrid($commitFilters);

        return $this->render('@Modules/kjdbvcs/views/templates/back/components/layouts/version-control.html.twig', [
            'changes_grid' => $this->presentGrid($changesGrid),
            'commits_grid' => $this->presentGrid($commitsGrid),
        ]);
    }
}
