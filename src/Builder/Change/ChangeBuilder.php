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

declare(strict_types=1);

namespace Kaudaj\Module\DBVCS\Builder\Change;

use PrestaShopBundle\Entity\Lang;
use PrestaShopBundle\Entity\Repository\LangRepository;
use Symfony\Component\Translation\TranslatorInterface;

abstract class ChangeBuilder implements ChangeBuilderInterface
{
    /**
     * @var Lang[]
     */
    protected $languages;

    /**
     * @var TranslatorInterface
     */
    protected $translator;

    public function __construct(TranslatorInterface $translator, LangRepository $langRepository)
    {
        /** @var Lang[] */
        $languages = $langRepository->findAll();

        $this->translator = $translator;
        $this->languages = $languages;
    }

    abstract public function buildUpMethod(): string;

    abstract public function buildDownMethod(): string;

    /**
     * {@inheritdoc}
     */
    public function getUseImports(): array
    {
        return [];
    }

    abstract public function getLocalizedDescriptions(): array;
}
