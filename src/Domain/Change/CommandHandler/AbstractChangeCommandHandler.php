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

namespace Kaudaj\Module\DBVCS\Domain\Change\CommandHandler;

use Doctrine\ORM\EntityManager;
use Doctrine\Persistence\ObjectRepository;
use Kaudaj\Module\DBVCS\Domain\Change\Exception\ChangeNotFoundException;
use Kaudaj\Module\DBVCS\Entity\Change;
use Kaudaj\Module\DBVCS\Entity\Commit;
use PrestaShopBundle\Entity\Repository\LangRepository;
use PrestaShopBundle\Entity\Repository\ShopGroupRepository;
use PrestaShopBundle\Entity\Repository\ShopRepository;
use PrestaShopDatabaseException;
use PrestaShopException;

/**
 * Class AbstractChangeCommandHandler.
 */
abstract class AbstractChangeCommandHandler
{
    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @var ObjectRepository<Change>
     */
    protected $entityRepository;

    /**
     * @var ObjectRepository<Commit>
     */
    protected $commitRepository;

    /**
     * @var LangRepository
     */
    protected $langRepository;

    /**
     * @var ShopRepository
     */
    protected $shopRepository;

    /**
     * @var ShopGroupRepository
     */
    protected $shopGroupRepository;

    public function __construct(
        EntityManager $entityManager,
        LangRepository $langRepository,
        ShopRepository $shopRepository,
        ShopGroupRepository $shopGroupRepository
    ) {
        $this->entityManager = $entityManager;

        /** @var ObjectRepository<Change> */
        $entityRepository = $this->entityManager->getRepository(Change::class);
        $this->entityRepository = $entityRepository;

        /** @var ObjectRepository<Commit> */
        $commitRepository = $this->entityManager->getRepository(Commit::class);
        $this->commitRepository = $commitRepository;

        $this->langRepository = $langRepository;
        $this->shopRepository = $shopRepository;
        $this->shopGroupRepository = $shopGroupRepository;
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
