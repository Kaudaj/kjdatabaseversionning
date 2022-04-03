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

namespace Kaudaj\Module\DBVCS;

use Doctrine\Migrations\Configuration\Configuration as MigrationsConfiguration;
use Kaudaj\Module\DBVCS\Builder\Change\ChangeBuilderInterface;
use Kaudaj\Module\DBVCS\Domain\Change\Command\AddChangeCommand;
use Kaudaj\Module\DBVCS\Domain\Change\Exception\ChangeException;
use Kaudaj\Module\DBVCS\Domain\Change\ValueObject\ChangeId;
use PrestaShop\PrestaShop\Adapter\Configuration;
use PrestaShop\PrestaShop\Core\CommandBus\CommandBusInterface;
use PrestaShop\PrestaShop\Core\Domain\Shop\ValueObject\ShopConstraint;
use RuntimeException;

class VersionControlManager
{
    private const ROOT_PATH = _PS_MODULE_DIR_ . 'kjdbvcs/';

    public const CHANGES_NAMESPACE = 'VersionControl\\Changes';
    public const COMMITS_NAMESPACE = 'VersionControl\\Commits';

    /**
     * @var CommandBusInterface
     */
    private $commandBus;

    public function __construct(CommandBusInterface $commandBus)
    {
        $this->commandBus = $commandBus;
    }

    /**
     * @throws ChangeException
     */
    public function registerChange(ChangeBuilderInterface $changeBuilder, ShopConstraint $shopConstraint): void
    {
        $localizedDescriptions = $changeBuilder->getLocalizedDescriptions();

        /** @var ChangeId */
        $changeId = $this->commandBus->handle(
            (new AddChangeCommand($shopConstraint))
                ->setLocalizedDescriptions($localizedDescriptions)
        );

        $configuration = new Configuration();
        $defaultLang = $configuration->getInt('PS_LANG_DEFAULT');

        $description = '';
        if (count($localizedDescriptions) > 0) {
            $description = key_exists($defaultLang, $localizedDescriptions)
                ? $localizedDescriptions[$defaultLang]
                : $localizedDescriptions[0]
            ;
        }

        $changePathname = self::ROOT_PATH . "version-control/changes/{$changeId->getValue()}.php";
        $changeSkeletonPathname = self::ROOT_PATH . 'src/Resources/skeletons/change.tpl.php';

        $changeContent = $this->parseSkeleton($changeSkeletonPathname, [
            'namespace' => self::CHANGES_NAMESPACE,
            'class_name' => "Change{$changeId->getValue()}",
            'description' => $description,
            'up' => str_replace(PHP_EOL, PHP_EOL . "\t\t", $changeBuilder->buildUpMethod()),
            'down' => str_replace(PHP_EOL, PHP_EOL . "\t\t", $changeBuilder->buildDownMethod()),
            'use_imports' => $changeBuilder->getUseImports(),
        ]);

        file_put_contents($changePathname, $changeContent);
    }

    public function commit(): void
    {
        // TODO: Implement
    }

    public function pull(): void
    {
        // TODO: Implement
    }

    public function getMigrationsConfiguration(): MigrationsConfiguration
    {
        $configuration = new MigrationsConfiguration();

        $configuration->addMigrationsDirectory(self::CHANGES_NAMESPACE, self::ROOT_PATH . 'version-control/changes');
        $configuration->addMigrationsDirectory(self::COMMITS_NAMESPACE, self::ROOT_PATH . 'version-control/commits');

        return $configuration;
    }

    /**
     * @param array<string, mixed> $parameters
     */
    public function parseSkeleton(string $skeletonPath, array $parameters): string
    {
        ob_start();
        extract($parameters, \EXTR_SKIP);
        include $skeletonPath;

        $content = ob_get_clean();
        if (!$content) {
            throw new RuntimeException('Failed to parse skeleton content.');
        }

        return $content;
    }
}
