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
use Kaudaj\Module\DBVCS\Repository\ChangeRepository;

/**
 * @ORM\Table(name=ChangeRepository::TABLE_NAME)
 * @ORM\Entity()
 */
class Change
{
    /**
     * @var int
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", name="id_change")
     */
    private $id;

    /**
     * @var int|null
     * @ORM\Column(type="integer", nullable=true)
     */
    private $commit;

    /**
     * @var \DateTimeInterface
     * @ORM\Column(type="datetime")
     */
    private $dateAdd;

    /**
     * @var ArrayCollection<int, ChangeLang>
     * @ORM\OneToMany(targetEntity=ChangeLang::class, mappedBy="change")
     */
    private $changeLangs;

    public function __construct()
    {
        $this->changeLangs = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getCommit(): ?int
    {
        return $this->commit;
    }

    public function setCommit(?int $commit): self
    {
        $this->commit = $commit;

        return $this;
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
     * @return ArrayCollection<int, ChangeLang>
     */
    public function getChangeLangs(): Collection
    {
        return $this->changeLangs;
    }

    public function addChangeLang(ChangeLang $changeLang): self
    {
        if (!$this->changeLangs->contains($changeLang)) {
            $this->changeLangs[] = $changeLang;

            $changeLang->setChange($this);
        }

        return $this;
    }

    public function removeChangeLang(ChangeLang $changeLang): self
    {
        $this->changeLangs->removeElement($changeLang);

        return $this;
    }
}
