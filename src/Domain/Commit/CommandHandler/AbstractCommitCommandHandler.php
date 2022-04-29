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

namespace Kaudaj\Module\DBVCS\Domain\Commit\CommandHandler;

use Doctrine\ORM\EntityManager;
use Doctrine\Persistence\ObjectRepository;
use Kaudaj\Module\DBVCS\Domain\Commit\Exception\CommitNotFoundException;
use Kaudaj\Module\DBVCS\Entity\Commit;
use PrestaShopBundle\Entity\Repository\LangRepository;
use PrestaShopDatabaseException;
use PrestaShopException;

/**
 * Class AbstractCommitCommandHandler.
 */
abstract class AbstractCommitCommandHandler
{
    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @var ObjectRepository<Commit>
     */
    protected $entityRepository;

    /**
     * @var LangRepository
     */
    protected $langRepository;

    public function __construct(
        EntityManager $entityManager,
        LangRepository $langRepository
    ) {
        $this->entityManager = $entityManager;

        /** @var ObjectRepository<Commit> */
        $entityRepository = $this->entityManager->getRepository(Commit::class);
        $this->entityRepository = $entityRepository;

        $this->langRepository = $langRepository;
    }

    /**
     * Gets commit entity.
     *
     * @throws CommitNotFoundException
     * @throws PrestaShopException
     * @throws PrestaShopDatabaseException
     */
    protected function getCommitEntity(int $id): Commit
    {
        /** @var Commit|null */
        $commit = $this->entityRepository->find($id);

        if (!$commit) {
            throw new CommitNotFoundException();
        }

        return $commit;
    }
}
