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

namespace Kaudaj\Module\DBVCS\Builder\Change\Module;

use Module;
use PrestaShopBundle\Entity\Repository\LangRepository;
use Symfony\Component\Translation\TranslatorInterface;

class ModuleInstallChangeBuilder extends ModuleChangeBuilder
{
    public function __construct(
        TranslatorInterface $translator,
        LangRepository $langRepository,
        Module $module
    ) {
        parent::__construct($translator, $langRepository, $module);
    }

    public function buildUpMethod(): string
    {
        return $this->buildCommonCode() . <<<CODE
\$moduleManager->install('{$this->module->name}');
CODE
        ;
    }

    public function buildDownMethod(): string
    {
        return $this->buildCommonCode() . <<<CODE
\$moduleManager->uninstall('{$this->module->name}');
CODE
        ;
    }

    public function getLocalizedDescriptions(): array
    {
        $localizedDescriptions = [];

        foreach ($this->languages as $lang) {
            $localizedDescriptions[$lang->getId()] = $this->translator->trans(
                'Installation of the %s module',
                [$this->module->name],
                'Modules.Kjdbvcs.Admin'
            );
        }

        return $localizedDescriptions;
    }

    private function buildCommonCode(): string
    {
        return <<<CODE
/** @var ModuleManager */
\$moduleManager = \$this->container->get('prestashop.module.manager');
CODE
        ;
    }
}
