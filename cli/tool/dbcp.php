<?php
ini_set('memory_limit', '2560M');
ini_set('max_execution_time', 3000000);

use ng169\cli\Clibase;
use ng169\db\daoClass;

require_once "../clibase.php";

class dbcp extends Clibase
{
    private $sourcePdo;
    private $targetPdo;
    private $dbalias1;
    private $dbalias2;
    private $table;
    private $batchSize = 100;
    private $isswoole;
    private $dxc=false;//多线程开关
    private $processes = [];

    private function checkswoole()
    {
        $this->isswoole = function_exists('pcntl_fork');
        if ($this->isswoole && $this->dxc) {
            d("支持多线程模式");
        } else {
            d("单线程模式");
        }
    }

    public function __construct($dbalias1, $dbalias2)
    {
        $this->checkswoole();
        $this->dbalias1 = $dbalias1;
        $this->dbalias2 = $dbalias2;
        $this->sourcePdo = new daoClass($dbalias1);
        // $this->targetPdo = new daoClass($dbalias2);
        $tables = $this->getargv(['table', 'size']);
        if (isset($tables['table'])) {
            $this->table = $tables['table'];
        }
        if (isset($tables['size'])) {
            $this->batchSize = $tables['size'];
        }
    }

    public function help()
    {
        d("支持指定参数size(同步插入速度),table(不带表前缀),不带参数表示同步所有表;命令实例 dbcp.php table=book");
    }

    public function synchronize()
    {
        $tables = $this->table ? [$this->table] : $this->sourcePdo->getalltable();
        $maxProcesses = 20; // 最大进程数
        if ($this->isswoole) {
        foreach ($tables as $table) {
                while (count($this->processes) >= $maxProcesses) {
                    $status = 0;
                    $pid = pcntl_waitpid(-1, $status);
                    if ($pid == -1) {
                        break;
                    }
                    $key = array_search($pid, $this->processes);
                    if ($key !== false) {
                        unset($this->processes[$key]);
                    }
                }

                $process = new DataSyncProcess($this->dbalias1, $this->dbalias2, $table, $this->batchSize, $this->isswoole, $this);
                $pid = pcntl_fork();
                if ($pid == -1) {
                    throw new Exception("Unable to fork process");
                } elseif ($pid > 0) {
                    $this->processes[] = $pid;
                } else {
                    $process->syncData();
                    exit(0);
                }
            }

            foreach ($this->processes as $pid) {
                pcntl_waitpid($pid, $status);
            }
        }else{
            foreach ($tables as $table) {
                $process=  new DataSyncProcess($this->dbalias1, $this->dbalias2, $table, $this->batchSize, $this->isswoole, $this) ;
                $process->syncData();
            } 
        }
    }
}

class DataSyncProcess
{
    private $db1;
    private $db2;
    private $table;
    private $batchSize;
    private $isswoole;
    private $dbcp;

    public function __construct($db1name, $db2name, $table, $batchSize, $isswoole, $dbcp)
    {
        $this->db1 = new daoClass($db1name);
        $this->db2 = new daoClass($db2name);
        $this->table = $table;
        $this->batchSize = $batchSize;
        $this->isswoole = $isswoole;
        $this->dbcp = $dbcp;
    }

  


    //开始同步表
    public function syncData()
    {
        if (!$this->tableExists($this->table)) {
            // 如果目标表不存在，则添加数据表
            $this->addtb($this->table);
        } else {
            $bool = $this->checktb($this->table);
        }
        // 检查表是否存在
        // 执行数据增量同步
        $this->aysndata();
    }
    //检查表结构是否一样
    private function tableExists($tb)
    {
        return $this->db2->havetable($tb);
    }
    private $allkey;
    private $dbtb2filed;
    //这里已经包含了表结构同步了
    private function checktb($tb)
    {
        $dbtb1 = $this->db1->gettableinfo($tb);
        $dbtb2 = $this->db2->gettableinfo($tb);
        if (!$this->dbtb2filed) {
            $this->dbtb2filed = array_column($dbtb2, 'Field');
        }

        // d($dbtb2,1);
        foreach ($dbtb1 as $key => $value) {

            $findindex = array_search($value['Field'], $this->dbtb2filed);
            if ($findindex !== false) {
                //找到到加不管了；
                //或者找到了继续判断属性类型、长度；默认值
                // echo "找到 '$search_value'，索引是 $index";  
            } else {
                $sql = "ALTER TABLE `" . $this->db2->getpre() . "_$tb` 
                ADD COLUMN `" . $value['Field'] . "` " . $value['Type'] . " " . ($value['Null'] == 'NO' ? " NOT NULL " : " NULL ") . " " . ($value['Default'] == "" ? "" : "DEFAULT " . $value['Default']) . "  " . ($value['Extra'] != "" ? $value['Extra'] : " ") . ";";
                $this->db2->exec($sql);
                unset($sql);
                // echo "'$search_value' 不在数组中";  
            }
            // unset($dbtb1);
            // unset($dbtb2);
            // unset($findindex);
            // unset($dbtb2filed);
            // gc_collect_cycles();

        }
    }

    //表不存在加添加数据表
    private function addtb($tb)
    {

        $this->tbname1 = $tbname1 = $this->db1->getpre() . $tb;
        $this->tbname2 = $tbname2 = $this->db2->getpre() . $tb;
        $sql = $this->db1->gettablesql($tb);
        if (!$sql) {
            return;
        }
        $sql = $sql["Create Table"];
        //替换表前缀
        $newCreateTableSql = str_replace($tbname1, $tbname2, $sql);
        d("添加表");

        // 获取源数据库的表结构
        $this->db2->exec($newCreateTableSql);
        unset($newCreateTableSql);
    }
    //数据增量同步；判断目标表的最大id值；以目标表的id值从源表开始同步
    private $pkey;
    private  $tbname1;
    private $tbname2;
    private function aysndata()
    {
        if ($this->allkey) {
            $pkeys = $this->allkey;
        } else {
            $this->allkey = $pkeys = $this->db1->gettableinfo($this->table);
        }

        $this->pkey = $pkeys[0]['Field'];
        $pkey = $this->pkey;
        // d($this->db2);
        $maxId1 = $this->getMaxId($this->db1, $this->table, $pkey);
        $maxId2 = $this->getMaxId($this->db2, $this->table, $pkey);
       
        if ($maxId1 <= $maxId2) {
            // d($this->table."数据量一样不用同步");
            return false;
        }
       
       
        d($this->table . "同步数据");
        $this->loopinsert($maxId2);
      
    }
    //开始循环插入；
    private function loopinsert($sid)
    {
        $llop = true;
        while ($llop) {
            $list = $this->getSourceData($this->db1, $this->table, $sid, $this->batchSize, $this->pkey);
           
            $num = sizeof($list);
            if ($num) {
                if($num<$this->batchSize){
                    $llop = false; 
                }
                $sid = $list[$num - 1][$this->pkey]; //从最后一条数据id开始；因为id可能不连续所以必须取最后一条
                $sql = "";
                foreach ($list as $key => $row) {
                    // $sql.= implode(', ', $row).",";  
                    $r = "";
                    $i = 0;
                    foreach ($row as $key2 => $value) {
                        //修复数据
                        $type = $this->allkey[$i]['Type'];
                        $i++;
                        $position = strpos($type, "int");
                        if ($position !== false) {
                            //int型
                            if ($value == "") {
                                $value = 0;
                            }
                        } elseif (strpos($type, "datetime") !== false) {
                            //时间型 
                            if ($value == "") {
                                $value = "2024-01-01";
                            }
                        }
                        $r .= '\'' . $value . '\',';
                    }
                    $r = rtrim($r, ",");
                    $sql .= "({$r}),";
                    // unset($r);
                }
                $sql = rtrim($sql, ",");
                $this->addsql($sql);
                // unset($list); // 释放 list 数组占用的内存
                // gc_collect_cycles(); // 触发垃圾回收
                // $this->loopinsert($lastmaxid);
            } else {
                $llop = false;
                d($this->table . "同步完成");
            }
        }
    }
    private $intbtmp;
    private function addsql($sql)
    {
        if ($this->intbtmp) {
            //   $this->intbtmp;
        } else {
            $table = $this->db2->getpre() . $this->table;
            $this->intbtmp = " INSERT  INTO {$table} VALUES ";
        }
        // $table=$this->db2->getpre().$this->table;
        $sql = $this->intbtmp .  $sql;
        try {
            $this->db2->exec($sql);
        } catch (\Throwable $th) {
            // d($th);
            d("插入失败".$this->table."id范围".$this->tmpmid."到".$this->batchSize);
            //throw $th;
        }



        // 执行更新操作，设置目标表的最大 ID 为当前最新同步的 ID
        // 这里需要根据实际的业务逻辑来确定如何更新最大 ID
    }
   private  $tmpmid;
    private function getSourceData($dao, $table, $maxId = 0, $batchSize, $pkey)
    {
        // 构建并执行查询以获取需要同步的数据
        $this->tmpmid=$maxId;
        $prefix = $dao->getpre(); // 假设 getpre 方法返回数据库表前缀
         $sql="SELECT * FROM `{$prefix}{$table}` WHERE `{$pkey}` >  $maxId  "."order by `{$pkey}` asc limit " .$batchSize  ;
        // $sql = "SELECT * FROM `{$prefix}{$table}` WHERE `{$pkey}` >  $maxId  " . " limit " . $batchSize;
        //顺序必须是主键上升
        $stmt = $dao->getall($sql);
        return $stmt;
    }
    private function getMaxId($dao, $table, $pkey)
    {
        $wztb = $dao->getpre() . $table;
        $sql = "SELECT MAX({$pkey}) as id FROM {$wztb}";
        $stmt = $dao->getone($sql);
        if (!$stmt['id']) return 0;
        return $stmt['id'];
    }
}

// 使用示例
$synchronizer = new dbcp('main', 'other');
$synchronizer->synchronize();
