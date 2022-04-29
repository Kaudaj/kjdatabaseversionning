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

namespace Kaudaj\Module\DBVCS\Grid\Definition\Factory;

use PrestaShop\PrestaShop\Core\Grid\Action\Row\RowActionCollection;
use PrestaShop\PrestaShop\Core\Grid\Action\Row\RowActionCollectionInterface;
use PrestaShop\PrestaShop\Core\Grid\Action\Row\RowActionInterface;
use PrestaShop\PrestaShop\Core\Grid\Action\Row\Type\SubmitRowAction;
use PrestaShop\PrestaShop\Core\Grid\Column\ColumnCollection;
use PrestaShop\PrestaShop\Core\Grid\Column\ColumnCollectionInterface;
use PrestaShop\PrestaShop\Core\Grid\Column\ColumnInterface;
use PrestaShop\PrestaShop\Core\Grid\Column\Type\DataColumn;
use PrestaShop\PrestaShop\Core\Grid\Definition\Factory\AbstractGridDefinitionFactory;

final class ChangeGridDefinitionFactory extends AbstractGridDefinitionFactory
{
    public const GRID_ID = 'changes';

    protected function getId()
    {
        return self::GRID_ID;
    }

    protected function getName()
    {
        return $this->trans('Changes', [], 'Modules.Kjdbvcs.Admin');
    }

    /**
     * @return ColumnCollectionInterface<ColumnInterface>
     */
    protected function getColumns()
    {
        return (new ColumnCollection())
            ->add((new DataColumn('id_change'))
                ->setName($this->trans('ID', [], 'Admin.Global'))
                ->setOptions([
                    'field' => 'id_change',
                ])
            )
            ->add((new DataColumn('description'))
                ->setName($this->trans('Description', [], 'Modules.Kjdbvcs.Admin'))
                ->setOptions([
                    'field' => 'description',
                ])
            )
            ->add((new DataColumn('commit'))
                ->setName($this->trans('Commit', [], 'Modules.Kjdbvcs.Admin'))
                ->setOptions([
                    'field' => 'id_commit',
                ])
            )
            ->add((new DataColumn('date_add'))
                ->setName($this->trans('Date', [], 'Modules.Kjdbvcs.Admin'))
                ->setOptions([
                    'field' => 'date_add',
                ])
            )
        ;
    }

    /**
     * @return RowActionCollectionInterface<RowActionInterface>
     */
    protected function getRowActions()
    {
        return (new RowActionCollection())
            ->add((new SubmitRowAction('delete'))
                ->setName($this->trans('Delete', [], 'Admin.Actions'))
                ->setIcon('delete')
                ->setOptions([
                    'method' => 'DELETE',
                    'route' => 'kj_dbvcs_change_delete',
                    'route_param_name' => 'changeId',
                    'route_param_field' => 'id_change',
                    'confirm_message' => $this->trans(
                        'Delete selected item?',
                        [],
                        'Admin.Notifications.Warning'
                    ),
                ])
            );
    }
}
