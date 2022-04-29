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

namespace Kaudaj\Module\DBVCS\Utils;

use Doctrine\Migrations\Configuration\Configuration as MigrationsConfiguration;
use Kaudaj\Module\DBVCS\Builder\Change\ChangeBuilderInterface;
use Kaudaj\Module\DBVCS\Domain\Change\Command\AddChangeCommand;
use Kaudaj\Module\DBVCS\Domain\Change\Command\EditChangeCommand;
use Kaudaj\Module\DBVCS\Domain\Change\Exception\ChangeException;
use Kaudaj\Module\DBVCS\Domain\Change\ValueObject\ChangeId;
use Kaudaj\Module\DBVCS\Domain\Commit\Command\AddCommitCommand;
use Kaudaj\Module\DBVCS\Domain\Commit\ValueObject\CommitId;
use Kaudaj\Module\DBVCS\Entity\Change;
use PrestaShop\PrestaShop\Adapter\Configuration;
use PrestaShop\PrestaShop\Core\CommandBus\CommandBusInterface;
use PrestaShop\PrestaShop\Core\Domain\Shop\ValueObject\ShopConstraint;
use RuntimeException;

class VersionControlManager
{
    private const ROOT_PATH = _PS_MODULE_DIR_ . 'kjdbvcs/';
    private const VERSION_CONTROL_DIR = self::ROOT_PATH . 'version-control/';

    public const CHANGES_NAMESPACE = 'VersionControl\\Changes';
    public const COMMITS_NAMESPACE = 'VersionControl\\Commits';

    public const ADDITIONAL_IMPORTS_OFFSET = 4;
    public const INDENTATION = 4;

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

        $changePathname = self::VERSION_CONTROL_DIR . "changes/{$changeId->getValue()}.php";
        $changeSkeletonPathname = self::ROOT_PATH . 'src/Resources/skeletons/change.tpl.php';

        $changeContent = $this->parseSkeleton($changeSkeletonPathname, [
            'namespace' => self::CHANGES_NAMESPACE,
            'class_name' => "Change{$changeId->getValue()}",
            'description' => $this->getDefaultDescription($localizedDescriptions),
            'use_imports' => $changeBuilder->getUseImports(),
            'up' => str_replace(PHP_EOL, PHP_EOL . str_repeat(' ', self::INDENTATION * 2), $changeBuilder->buildUpMethod()),
            'down' => str_replace(PHP_EOL, PHP_EOL . str_repeat(' ', self::INDENTATION * 2), $changeBuilder->buildDownMethod()),
        ]);

        file_put_contents($changePathname, $changeContent);
    }

    /**
     * @param Change[] $changes
     * @param array<int, string> $localizedDescriptions
     */
    public function commit(array $changes, array $localizedDescriptions): void
    {
        /** @var CommitId */
        $commitId = $this->commandBus->handle(
            (new AddCommitCommand())
                ->setLocalizedDescriptions($localizedDescriptions)
        );

        [$useImports, $upContent, $downContent] = [[], '', ''];

        foreach ($changes as $change) {
            $changeId = $change->getId();

            $this->commandBus->handle(
                (new EditChangeCommand($changeId))
                    ->setCommitId($commitId->getValue())
            );

            $changePathname = self::VERSION_CONTROL_DIR . "changes/$changeId.php";
            $changeContent = file_get_contents($changePathname);

            if (!$changeContent) {
                throw new RuntimeException('Failed to parse change content.');
            }

            $fileParser = new FileParser($changeContent);

            $useImports = array_merge(
                $useImports,
                $fileParser->getUseImports(self::ADDITIONAL_IMPORTS_OFFSET)
            );

            $upContent .= $fileParser->getClassMethodContent('up') . PHP_EOL . str_repeat(' ', self::INDENTATION * 2);
            $downContent .= $fileParser->getClassMethodContent('down') . PHP_EOL . str_repeat(' ', self::INDENTATION * 2);
        }

        $commitPathname = self::VERSION_CONTROL_DIR . "commits/{$commitId->getValue()}.php";
        $commitSkeletonPathname = self::ROOT_PATH . 'src/Resources/skeletons/commit.tpl.php';

        $commitContent = $this->parseSkeleton($commitSkeletonPathname, [
            'namespace' => self::COMMITS_NAMESPACE,
            'class_name' => "Commit{$commitId->getValue()}",
            'description' => $this->getDefaultDescription($localizedDescriptions),
            'use_imports' => array_unique($useImports),
            'up' => rtrim($upContent),
            'down' => rtrim($downContent),
        ]);

        file_put_contents($commitPathname, $commitContent);
    }

    public function pull(): void
    {
        // TODO: Implement
    }

    public function getMigrationsConfiguration(): MigrationsConfiguration
    {
        $configuration = new MigrationsConfiguration();

        $configuration->addMigrationsDirectory(self::CHANGES_NAMESPACE, self::VERSION_CONTROL_DIR . 'changes');
        $configuration->addMigrationsDirectory(self::COMMITS_NAMESPACE, self::VERSION_CONTROL_DIR . 'commits');

        return $configuration;
    }

    /**
     * @param array<int, string> $localizedDescriptions
     */
    private function getDefaultDescription(array $localizedDescriptions): string
    {
        $configuration = new Configuration();
        $defaultLang = $configuration->getInt('PS_LANG_DEFAULT');

        if (empty($localizedDescriptions)) {
            return '';
        }

        return key_exists($defaultLang, $localizedDescriptions)
            ? $localizedDescriptions[$defaultLang]
            : $localizedDescriptions[0]
        ;
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
