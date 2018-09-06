<?php
namespace Mig\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;

use Mig\Config;

/**
 * Command for create a new migration sql file.
 *
 */
class CreateCommand extends Command
{

    protected function configure()
    {
        $this->setName('create')
            ->setDescription('Create a new migration file.')
            ->addArgument('filename', InputArgument::REQUIRED, 'name of migration sql file.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $config = new Config();
        $pdo = new \PDO(
            $config->getDbDsn(),
            $config->getDbUsername(),
            $config->getDbPasswd()
        );

        $output->writeln([
            "Create a new migration file.",
            '=================',
            '',
        ]);

        if(!file_exists($config->getMigrationFilePath()))
        {
            mkdir($config->getMigrationFilePath(), 0755, true);
        }

        $fname =  date('YmdHis') . '_' . $input->getArgument('filename');
        $f = $config->getMigrationFilePath() . '/'. $fname;
        $upSqlFile = $f . '.up.sql';
        $downSqlFile = $f . '.down.sql';

        touch($upSqlFile);
        touch($downSqlFile);
        $output->writeln('create '. $upSqlFile);
        $output->writeln('create '. $downSqlFile);
    }
}