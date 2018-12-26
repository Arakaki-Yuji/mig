<?php

namespace Mig\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;

use Mig\Config;
use Mig\Core;

class RollbackCommand extends Command
{
    protected function configure()
    {
        $this->setName('rollback')
            ->setDescription('Rollback migrations.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $config = new Config();
        $core = new Core();
        $output->writeln([
            'Rollback a migration.',
            '======================',
            ''
        ]);
        $last_migration_id = $core->last_migration($core->make_pdo($config));
        if($last_migration_id){
            $down_migrations = $core->find_down_migration_file($config, $last_migration_id);
            foreach($down_migrations as $m)
            {
                $tmp = explode('/', $m);
                $filename = $tmp[count($tmp) - 1];
                $sql = file_get_contents($m);
                if(empty($sql)){
                    throw new \Exception('Failed to run the migration '.$m . ': trying to execute an empty query');
                }
                $pdo = $core->make_pdo($config);
                $pdo->beginTransaction();
                try {
                    $queries = preg_split("/;\n/", $sql);
                    foreach($queries as $query){
                        if(!empty($query)){
                            $pdo->exec($query);
                            if($pdo->errorInfo()[2])
                            {
                                throw new \Exception('Failed to run rollback '.$m . ': '.$pdo->errorInfo()[2]);
                            }
                        }
                    }
                    $core->delete_migration($pdo, $last_migration_id);
                    $pdo->commit();
                    $output->writeln('Rollback '.$m);
                }catch(\Exception $e){
                    $pdo->rollBack();
                    throw $e;
                }
            }
        }
    }

}
