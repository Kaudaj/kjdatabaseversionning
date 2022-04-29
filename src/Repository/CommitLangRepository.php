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

namespace Kaudaj\Module\DBVCS\Repository;

use Doctrine\ORM\EntityRepository;
use Kaudaj\Module\DBVCS\Entity\CommitLang;

/**
 * @method CommitLang|null find($id, $lockMode = null, $lockVersion = null)
 * @method CommitLang|null findOneBy(array $criteria, array $orderBy = null)
 * @method CommitLang[] findAll()
 * @method CommitLang[] findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @extends EntityRepository<CommitLang>
 */
class CommitLangRepository extends EntityRepository
{
    public const TABLE_NAME = _DB_PREFIX_ . 'kj_dbvcs_commit_lang';
}
