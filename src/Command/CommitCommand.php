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

namespace Kaudaj\Module\DBVCS\Command;

use Exception;
use Kaudaj\Module\DBVCS\Domain\Change\Query\GetChange;
use Kaudaj\Module\DBVCS\Utils\VersionControlManager;
use PrestaShop\PrestaShop\Adapter\Configuration;
use PrestaShop\PrestaShop\Core\CommandBus\CommandBusInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class CommitCommand extends Command
{
    /**
     * @var VersionControlManager
     */
    private $versionControlManager;

    /**
     * @var CommandBusInterface
     */
    private $commandBus;

    protected static $defaultName = 'kj:dbvcs:commit';

    public function __construct(VersionControlManager $versionControlManager, CommandBusInterface $commandBus)
    {
        parent::__construct();

        $this->versionControlManager = $versionControlManager;
        $this->commandBus = $commandBus;
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Pack up the changes in a commit')
            ->addArgument('changes', InputArgument::REQUIRED | InputArgument::IS_ARRAY, 'Changes ids')
            ->addOption('message', 'm', InputOption::VALUE_REQUIRED, 'Commit message')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $changesIds = (array) $input->getArgument('changes');
        $changes = [];

        foreach ($changesIds as $changeId) {
            try {
                $changes[] = $this->commandBus->handle(new GetChange((int) $changeId));
            } catch (Exception $e) {
                $io->error(sprintf('Failed to retrieve change with id %s.', $changeId));

                return 1;
            }
        }

        $message = $input->getOption('message');
        if (!$message) {
            do {
                $message = $io->ask('Please provide a commit message:');

                if (!$message) {
                    $io->error("Commit message can't be empty.");
                }
            } while (!$message);
        }

        $configuration = new Configuration();
        $defaultLanguage = $configuration->getInt('PS_LANG_DEFAULT');

        $this->versionControlManager->commit($changes, [$defaultLanguage => strval($message)]);

        return 0;
    }
}
