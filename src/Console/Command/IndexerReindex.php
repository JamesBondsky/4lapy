<?php

namespace FourPaws\Console\Command;

use FourPaws\App\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

class IndexerReindex extends Command
{
    const OPT_FORCE = 'force';
    const OPT_NO_FILTER = 'no-filter';
    private $searchService;

    /**
     * ElasticReindexAll constructor.
     *
     * @param null|string $name
     *
     * @throws \FourPaws\App\Exceptions\ApplicationCreateException
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException
     */
    public function __construct(string $name = null)
    {
        parent::__construct($name);
        $this->searchService = Application::getInstance()->getContainer()->get('search.service');
    }

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this
            ->setName('indexer:reindex')
            ->setDescription('Reindex all catalog in Elasticsearch. Also could create index if it doesn\'t exist.')
            ->addOption(
                self::OPT_FORCE,
                'f',
                InputOption::VALUE_NONE,
                'Recreate catalog index. Useful if you want to apply new mapping'
            )
            ->addOption(
                static::OPT_NO_FILTER,
                'nf',
                InputOption::VALUE_NONE,
                'Index without any filter'
            );
    }

    /**
     * @inheritdoc
     */
    protected function interact(InputInterface $input, OutputInterface $output)
    {
        if ($input->getOption(self::OPT_FORCE)) {
            $helper = $this->getHelper('question');
            $question = new ConfirmationQuestion(
                'Are you sure you want to DELETE catalog index? [y/N]:',
                false
            );

            if (!$helper->ask($input, $output, $question)) {
                $output->writeln('Execution aborted.');
                die(0);
            }
        }
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $force = $input->getOption(self::OPT_FORCE);
        if ($force) {
            $output->writeln('FORCE MODE: Catalog index will be destroyed and created again!');
        }
        $noFilter = $input->getOption(self::OPT_FORCE) ?: false;

        if ($this->searchService->getIndexHelper()->createCatalogIndex($force)) {
            $output->writeln(
                sprintf(

                    'Catalog index created %s',
                    $this->searchService->getIndexHelper()->getCatalogIndex()->getName()
                )
            );
        }
        $this->searchService->getIndexHelper()->indexAll($noFilter);
        $this->searchService->getIndexHelper()->cleanup($noFilter);
    }
}
