<?php

namespace Mig;

class Core
{
    public function make_pdo(Config $config){
        return new \PDO(
            $config->getDbDsn(),
            $config->getDbUsername(),
            $config->getDbPasswd()
        );
    }

    public function last_migration(\PDO $pdo)
    {
        $stmts = $pdo->query('SELECT * FROM migrations ORDER BY id DESC LIMIT 1');
        $records = $stmts->fetchAll();
        if($records && count($records) > 0){
            return $records[0]['id'];
        }else{
            return 0;
        };
    }

    public function get_timestamp($filename)
    {
        $r = preg_match('/^[0-9]*/', $filename, $matches);
        if($r === 1){
            return $matches[0];
        }else{
            return 0;
        }
    }

    public function list_migrations_doesnot_ran(Config $config, $migration_id)
    {
        $files_path = $this->ls($config->getMigrationFilePath());
        $files_path = $this->sort_files_path_by_timestamp($files_path);
        $only_up_files = array_filter($files_path, function($f){
            $r = explode('.', $f);
            if($r === false){
                return false;
            }
            if($r[count($r) -2] === 'up'){
                return true;
            }else{
                return false;
            }
        });
        $migrations_didnot_run = array_filter($only_up_files, function($f) use ($migration_id){
            $tmp = explode('/', $f);
            $fname = $tmp[count($tmp) - 1];
            $t = $this->get_timestamp($fname);
            return (int)$t > (int) $migration_id;
        });
        return $migrations_didnot_run;
    }

    public function filename_from_path($path)
    {
        $tmp = explode('/', $path);
        return $tmp[count($tmp) - 1];
    }

    public function sort_files_path_by_timestamp($files_path)
    {
        usort($files_path, function($a, $b){
            $at = (int)$this->get_timestamp($this->filename_from_path($a));
            $bt = (int)$this->get_timestamp($this->filename_from_path($b));
            if($at == $bt){
                return 0;
            }
            return $at < $bt ? -1 : 1;
        });
        return $files_path;
    }

    public function find_down_migration_file($config, $id)
    {
        $files_path = $this->ls($config->getMigrationFilePath());
        $id_filterd = array_filter($files_path, function($f) use ($id){
            $tmp = explode('/', $f);
            $fname = $tmp[count($tmp) - 1];
            $t = $this->get_timestamp($fname);
            return (int)$t == (int) $id;
        });
        $down_files = array_filter($id_filterd, function($f){
            $r = explode('.', $f);
            if($r === false){
                return false;
            }
            if($r[count($r) -2] === 'down'){
                return true;
            }else{
                return false;
            }
        });
        return $down_files;
    }

    public function ls($dir)
    {
        $files_path = [];
        if( is_dir( $dir ) && $handle = opendir( $dir ) ) {
            while( ($file = readdir($handle)) !== false ) {
                if( filetype( $path = $dir . '/'. $file ) == "file" ) {
                    // $path: ファイルのパス
                    $files_path[] = $path;
                }
            }
        }
        return $files_path;
    }

}