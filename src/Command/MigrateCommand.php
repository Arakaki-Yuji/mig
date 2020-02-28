<?php

namespace Mig\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;

use Mig\Config;
use Mig\Core;

class MigrateCommand extends Command
{
    protected function configure()
    {
        $this->setName('migrate')
            ->setDescription('Migrate scheme.');
        $this->addArgument('option', InputArgument::OPTIONAL, 'migrate option.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $config = new Config();
        $core = new Core();
        $output->writeln([
            'Start migration.',
            '=================',
            ''
        ]);
        $skip_dep_exst = false;
        $option = $input->getArgument('option');
        if($option === 'skip-duplicate-and-exists-errors'){
            $skip_dep_exst = true;
        }

        $last_migration = $core->last_migration($core->make_pdo($config));
        $up_migrations = $core->list_migrations_doesnot_ran($config, $last_migration);
        foreach($up_migrations as $m)
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
                    if(!empty(trim($query))){
                        $pdo->exec($query);
                        if($pdo->errorInfo()[2]){
                            $errMsg = $pdo->errorInfo()[2];
                            if($this->is_duplicate_and_exist_error($errMsg) &&
                               $skip_dep_exst){
                                $output->writeln('Skipped to run the migration '. $m . ': '. $pdo->errorInfo()[2]);
                            }else{
                                throw new \Exception('Failed to run the migration '. $m . ': '. $pdo->errorInfo()[2]);
                            }
                        }
                    }
                }
                $id = $core->get_timestamp($filename);
                $core->update_last_migration_timestamp($pdo, $id);
                $pdo->commit();
                $output->writeln('Migrate '.$m);
            }catch(\Exception $e){
                $pdo->rollBack();
                throw $e;
            }
        }
    }

    public function is_duplicate_and_exist_error($errMsg)
    {
        $dup_column = '/^Duplicate column name/';
        $dup_table = '/^Table.+already exists$/';
        $dup_key = '/^Duplicate key name/';
        $dup_entry = '/^Duplicate entry/';
        $patterns = [
            $dup_column,
            $dup_table,
            $dup_key,
            $dup_entry
        ];
        foreach($patterns as $p){
            if(preg_match($p, $errMsg) === 1){
                return true;
            }
        }
        return false;
    }

}
