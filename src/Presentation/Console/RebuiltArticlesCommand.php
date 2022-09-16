<?php
declare(strict_types=1);
namespace App\Presentation\Console;

use App\Application\ArticlesService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:clear')]
class RebuiltArticlesCommand extends Command
{
    public function __construct(
        readonly protected ArticlesService $articlesService
    )
    {
        parent::__construct($this->getName());
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln("Clearing articles cache");
        $this->articlesService->clearCache();
        $output->writeln("Writing new cache");
        $this->articlesService->initCache();

        $output->writeln("All done");
        return Command::SUCCESS;
    }
}