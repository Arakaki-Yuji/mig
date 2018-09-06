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
class InitCommand extends Command
{

    protected function configure()
    {
        $this->setName('init')
            ->setDescription('Initialize for manage migration.');
    }

    private function create_migrations_table(\PDO $pdo)
    {
        $sql = 'CREATE TABLE IF NOT EXISTS `migrations` '.
             '(id BIGINT UNIQUE NOT NULL,'.
             'applied_at int(11))';
        $pdo->exec($sql);
    }

    private function exist_migrations_table(\PDO $pdo)
    {
        $r = $pdo->exec('SELECT id, applied_at FROM migrations LIMIT 1');
        return $r === false ? false : true;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $c = new Config();
        $output->writeln([
            "Initialize for manage migration.",
            '=================',
            '',
        ]);
        $pdo = new \PDO(
            $c->getDbDsn(),
            $c->getDbUsername(),
            $c->getDbPasswd()
        );

        $r = $this->exist_migrations_table($pdo);
        if(!$r){
            $this->create_migrations_table($pdo);
            $r = $this->exist_migrations_table($pdo);
            if($r){
                $output->writeln('Create migrations table.');
            }else{
                $output->writeln('Failed to create migrations table.');
            }
        }else{
            $output->writeln('Already created migrations table.');
        }
    }
}
