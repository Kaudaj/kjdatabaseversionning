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

use Doctrine\ORM\EntityManager;
use Doctrine\Persistence\ObjectRepository;
use Kaudaj\Module\DBVCS\Domain\Change\Exception\ChangeNotFoundException;
use Kaudaj\Module\DBVCS\Entity\Change;
use PrestaShopDatabaseException;
use PrestaShopException;

/**
 * Class AbstractChangeQueryHandler.
 */
abstract class AbstractChangeQueryHandler
{
    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @var ObjectRepository<Change>
     */
    protected $entityRepository;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;

        /** @var ObjectRepository<Change> */
        $entityRepository = $this->entityManager->getRepository(Change::class);

        $this->entityRepository = $entityRepository;
    }

    /**
     * Gets change entity.
     *
     * @throws ChangeNotFoundException
     * @throws PrestaShopException
     * @throws PrestaShopDatabaseException
     */
    protected function getChangeEntity(int $id): Change
    {
        /** @var Change|null */
        $change = $this->entityRepository->find($id);

        if (!$change) {
            throw new ChangeNotFoundException();
        }

        return $change;
    }
}
