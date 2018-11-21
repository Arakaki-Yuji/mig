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
                    if(!empty($query)){
                        $pdo->exec($query);
                        if($pdo->errorInfo()[2]){
                            throw new \Exception('Failed to run the migration '. $m . ': '. $pdo->errorInfo()[2]);
                        }
                    }
                }
                $id = $core->get_timestamp($filename);
                $this->update_last_migration_timestamp($pdo, $id);
                $pdo->commit();
                $output->writeln('Migrate '.$m);
            }catch(\Exception $e){
                $pdo->rollBack();
                throw $e;
            }
        }
    }

    private function update_last_migration_timestamp(\PDO $pdo, $id)
    {
        if(!$id){
            return false;
        }
        $sql = 'INSERT INTO migrations(id, applied_at) VALUES (:id, :applied_at)';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id', $id);
        $stmt->bindValue(':applied_at', time());
        return $stmt->execute();
    }
}
