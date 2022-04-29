<?php
/**
 * Copyright since 2019 Kaudaj.
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

namespace Kaudaj\Module\DBVCS\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Kaudaj\Module\DBVCS\Repository\CommitRepository;

/**
 * @ORM\Table(name=CommitRepository::TABLE_NAME)
 * @ORM\Entity()
 */
class Commit
{
    /**
     * @var int
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", name="id_commit")
     */
    private $id;

    /**
     * @var \DateTimeInterface
     * @ORM\Column(type="datetime")
     */
    private $dateAdd;

    /**
     * @var ArrayCollection<int, CommitLang>
     * @ORM\OneToMany(targetEntity=CommitLang::class, cascade={"persist", "remove"}, mappedBy="commit")
     */
    private $commitLangs;

    public function __construct()
    {
        $this->commitLangs = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getDateAdd(): \DateTimeInterface
    {
        return $this->dateAdd;
    }

    public function setDateAdd(\DateTimeInterface $dateAdd): self
    {
        $this->dateAdd = $dateAdd;

        return $this;
    }

    /**
     * @return ArrayCollection<int, CommitLang>
     */
    public function getCommitLangs(): Collection
    {
        return $this->commitLangs;
    }

    public function addCommitLang(CommitLang $commitLang): self
    {
        if (!$this->commitLangs->contains($commitLang)) {
            $this->commitLangs[] = $commitLang;

            $commitLang->setCommit($this);
        }

        return $this;
    }

    public function removeCommitLang(CommitLang $commitLang): self
    {
        $this->commitLangs->removeElement($commitLang);

        return $this;
    }
}
