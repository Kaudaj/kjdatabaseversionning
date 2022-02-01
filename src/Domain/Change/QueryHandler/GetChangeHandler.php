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

namespace Kaudaj\Module\DBVCS\Domain\Change\QueryHandler;

use Kaudaj\Module\DBVCS\Domain\Change\Exception\ChangeException;
use Kaudaj\Module\DBVCS\Domain\Change\Exception\ChangeNotFoundException;
use Kaudaj\Module\DBVCS\Domain\Change\Query\GetChange;
use Kaudaj\Module\DBVCS\Entity\Change;
use PrestaShopException;

/**
 * Class GetChangeHandler is responsible for getting change entity.
 *
 * @internal
 */
final class GetChangeHandler extends AbstractChangeQueryHandler
{
    /**
     * @throws PrestaShopException
     * @throws ChangeNotFoundException
     */
    public function handle(GetChange $query): Change
    {
        try {
            $change = $this->getChangeEntity(
                $query->getChangeId()->getValue()
            );
        } catch (PrestaShopException $e) {
            throw new ChangeException(sprintf('An unexpected error occurred when retrieving change with id %s', var_export($query->getChangeId()->getValue(), true)), 0, $e);
        }

        return $change;
    }
}
