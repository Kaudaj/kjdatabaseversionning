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

use Kaudaj\Module\DBVCS\Domain\Change\Command\AddChangeCommand;
use Kaudaj\Module\DBVCS\Domain\Change\Exception\CannotAddChangeException;
use Kaudaj\Module\DBVCS\Domain\Change\Exception\ChangeException;
use Kaudaj\Module\DBVCS\Domain\Change\ValueObject\ChangeId;
use Kaudaj\Module\DBVCS\Entity\Change;
use Kaudaj\Module\DBVCS\Entity\ChangeLang;
use Kaudaj\Module\DBVCS\Entity\Commit;
use PrestaShopBundle\Entity\Lang;
use PrestaShopBundle\Entity\Shop;
use PrestaShopBundle\Entity\ShopGroup;
use PrestaShopException;

/**
 * Class AddChangeHandler is used for adding change data.
 */
final class AddChangeHandler extends AbstractChangeCommandHandler
{
    /**
     * @throws CannotAddChangeException
     * @throws ChangeException
     */
    public function handle(AddChangeCommand $command): ChangeId
    {
        try {
            $entity = new Change();

            $commitId = $command->getCommitId();
            if (null !== $commitId) {
                /** @var Commit|null */
                $commit = $this->commitRepository->find($commitId->getValue());

                $entity->setCommit($commit);
            }

            $shopId = $command->getShopConstraint()->getShopId();
            if (null !== $shopId) {
                /** @var Shop|null */
                $shop = $this->shopRepository->find($shopId->getValue());

                $entity->setShop($shop);
            }

            $shopGroupId = $command->getShopConstraint()->getShopId();
            if (null !== $shopGroupId) {
                /** @var ShopGroup|null */
                $shopGroup = $this->shopGroupRepository->find($shopGroupId->getValue());

                $entity->setShopGroup($shopGroup);
            }

            foreach ($command->getLocalizedDescriptions() as $langId => $localizedDescription) {
                $changeLang = new ChangeLang();

                /** @var Lang|null */
                $lang = $this->langRepository->find($langId);
                if (null === $lang) {
                    continue;
                }

                $changeLang->setLang($lang);
                $changeLang->setDescription($localizedDescription->getValue());

                $entity->addChangeLang($changeLang);
            }

            $entity->setDateAdd(new \DateTime('now', new \DateTimeZone('UTC')));

            $this->entityManager->persist($entity);
            $this->entityManager->flush();
        } catch (PrestaShopException $exception) {
            throw new ChangeException('An unexpected error occurred when adding change', 0, $exception);
        }

        return new ChangeId((int) $entity->getId());
    }
}
