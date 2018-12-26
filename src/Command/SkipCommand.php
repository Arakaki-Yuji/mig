<?php

namespace Mig\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;

use Mig\Config;
use Mig\Core;


class SkipCommand extends Command
{
    protected function configure()
    {
        $this->setName('skip')
            ->setDescription('Skip migrate or rollback.')
            ->addArgument('cmd', InputArgument::REQUIRED, 'cmd what migrate or rollback')
            ->addArgument('filename', InputArgument::REQUIRED, 'file name what you want to skip migration');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $cmd = $input->getArgument('cmd');
        if($cmd === 'migrate')
        {
            $this->skip_migrate($input, $output);
        }else if($cmd === 'rollback')
        {
            $this->skip_rollback($input, $output);
        }else{
            throw new \Exception('argument <cmd> must be migrate or rollback.');
        }
    }

    private function skip_migrate(InputInterface $input, OutputInterface $output)
    {
        $config = new Config();
        $core = new Core();
        $output->writeln([
            'Skip migration.',
            '=================',
            ''
        ]);

        $fname = $core->filename_from_path($input->getArgument('filename'));
        $timestamp = $core->get_timestamp($fname);
        $pdo = $core->make_pdo($config);
        $core->update_last_migration_timestamp($pdo, $timestamp);
        $output->writeln([
            $fname
        ]);
    }

    private function skip_rollback(InputInterface $input, OutputInterface $output)
    {
        $config = new Config();
        $core = new Core();
        $output->writeln([
            'Skip rollback.',
            '=================',
            ''
        ]);

        $fname = $core->filename_from_path($input->getArgument('filename'));
        $timestamp = $core->get_timestamp($fname);
        $pdo = $core->make_pdo($config);
        $core->delete_migration($pdo, $timestamp);
        $output->writeln([
            $fname
        ]);
    }

}
