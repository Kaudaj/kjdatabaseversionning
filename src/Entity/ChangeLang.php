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

namespace Kaudaj\Module\DBVCS\Entity;

use Doctrine\ORM\Mapping as ORM;
use Kaudaj\Module\DBVCS\Repository\ChangeLangRepository;
use PrestaShopBundle\Entity\Lang;

/**
 * @ORM\Table(name=ChangeLangRepository::TABLE_NAME)
 * @ORM\Entity()
 */
class ChangeLang
{
    /**
     * @var Change
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity=Change::class, inversedBy="changeLangs")
     * @ORM\JoinColumn(name="id_change", referencedColumnName="id_change", nullable=false, onDelete="CASCADE")
     */
    private $change;

    /**
     * @var Lang
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity=Lang::class)
     * @ORM\JoinColumn(nullable=false)
     * @ORM\JoinColumn(name="id_lang", referencedColumnName="id_lang", nullable=false, onDelete="CASCADE")
     */
    private $lang;

    /**
     * @var string
     * @ORM\Column(type="string", length=255)
     */
    private $description;

    public function getChange(): Change
    {
        return $this->change;
    }

    public function setChange(Change $change): self
    {
        $this->change = $change;

        return $this;
    }

    public function getLang(): Lang
    {
        return $this->lang;
    }

    public function setLang(Lang $lang): self
    {
        $this->lang = $lang;

        return $this;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }
}
