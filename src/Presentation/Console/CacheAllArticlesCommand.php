<?php
declare(strict_types=1);
namespace App\Presentation\Console;

use App\Application\ArticlesService;
use App\Infrastructure\Cache\CacheService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:cache:populate')]
class CacheAllArticlesCommand extends Command
{
    public function __construct(
        readonly protected ArticlesService $articlesService,
        readonly protected CacheService $cacheService
    )
    {
        parent::__construct($this->getName());
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln("Cache Store");
        $filePath = 'resources/articles/20220709_slug.md';
        //dump($this->articlesService->getAll());
        //$cachedArticle = $this->cacheService->storeItem($filePath);
        //$output->writeln("cache write suceeded, trying to read");
        //$cachedArticleCopy = $this->cacheService->getItem($filePath);

        //dump($cachedArticle->getFrontMatter() == $cachedArticleCopy->getFrontMatter());

        return Command::SUCCESS;
    }
}