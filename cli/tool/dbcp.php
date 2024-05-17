<?php

use ng169\cli\Clibase;
use ng169\db\daoClass;
use ng169\lib\Option;

require_once "../clibase.php";
//数据库同步
//先判断表是否一样；不一样加修改成一样
class dbcp extends Clibase
{
    private $sourcePdo;
    private $targetPdo;
    private $dbalias1;
    private $dbalias2;
    private $table;
    private $batchSize=1000;
    public function __construct($dbalias1, $dbalias2)
    {
        $this->dbalias1 =  ($dbalias1);
        $this->dbalias2 =  ($dbalias2);
        $this->sourcePdo = new daoClass($dbalias1);
        $this->targetPdo = new daoClass($dbalias2);
        $tables= $this->getargv(['table','size']);
        if(isset($tables['table'])){
            $this->table = $tables['table'];
        }
        if(isset($tables['size'])){
            $this->batchSize = $tables['size'];
        }
    }
    public function help(){
        d("支持指定参数size(同步插入速度),table(不带表前缀),不带参数表示同步所有表;命令实例 dbcp.php table=book");
    }
    public function synchronize()
    {
        // 获取需要同步的数据
        if($this->table){
            $tables =[$this->table];
        }else{
            $tables = $this->sourcePdo->getalltable();
        }
        // 多进程处理数据同步
        $processes = [];
        foreach ($tables as $table) {
            $processes[] = new DataSyncProcess($this->dbalias1, $this->dbalias2, $table,$this->batchSize);
        }
        // 启动多进程
        foreach ($processes as $process) {
            $process->start();
        }
        // 等待所有进程完成
        foreach ($processes as $process) {
            $process->join();
        }
    }
}

class DataSyncProcess
{
    private $db1;
    private $db2;
    private $table;
    private $process;
    private $batchSize = 1000;
    function getpdo($dbalias)
    {
        return new daoClass($dbalias);
    }
    public function __construct($db1name, $db2name, $table,$batchSize)
    {
        $this->db1 = $this->getpdo($db1name);
        $this->db2 = $this->getpdo($db2name);
        $this->table = $table;
        $this->batchSize = $batchSize;
        //   start();
    }

    public function start()
    {
        // 创建一个新的进程

        if (function_exists('swoole_process')) {
            d("开启swoole多线程模式");
            $this->process = new \swoole_process(function () {
                $this->syncData();
            }, true);
            $this->process->start();
        } else {
            d("当前没装swoole扩展；开启单线程模式");
            $this->syncData();
        }
    }

    public function join()
    {
        // 等待进程结束
        if ($this->process)
            $this->process->join();
    }
    //开始同步表
    private function syncData()
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
    //这里已经包含了表结构同步了
    private function checktb($tb)
    {
        $dbtb1 = $this->db1->gettableinfo($tb);

        $dbtb2 = $this->db2->gettableinfo($tb);
        $dbtb2filed = array_column($dbtb2, 'Field');
        // d($dbtb2,1);
        foreach ($dbtb1 as $key => $value) {
            $findindex = array_search($value['Field'], $dbtb2filed);
            if ($findindex !== false) {
                //找到到加不管了；
                //或者找到了继续判断属性类型、长度；默认值

                // echo "找到 '$search_value'，索引是 $index";  
            } else {
                $sql = "ALTER TABLE `" . $this->db2->getpre() . "_$tb` 
                ADD COLUMN `" . $value['Field'] . "` " . $value['Type'] . " " .( $value['Null'] == 'NO' ? " NOT NULL " : " NULL " ). " " . ($value['Default'] == "" ? "" : "DEFAULT " . $value['Default']) . "  " . ($value['Extra'] != "" ? $value['Extra'] : " ") . ";";
                $this->db2->exec($sql);
                // echo "'$search_value' 不在数组中";  
            }
        }
    }
    //表结构不一样加修改成表使同步
    private function changetb($tb)
    {
    }
    //表不存在加添加数据表
    private function addtb($tb)
    {
        
        $this->tbname1=$tbname1= $this->db1->getpre().$tb;
        $this->tbname2= $tbname2= $this->db2->getpre().$tb;
        $sql=$this->db1->gettablesql($tb);
        if(!$sql){return;}
        $sql=$sql["Create Table"];
        //替换表前缀
        $newCreateTableSql = str_replace($tbname1, $tbname2, $sql);
        d("添加表");
        // d($newCreateTableSql,1);
        // 获取源数据库的表结构
        $this->db2->exec($newCreateTableSql);
    }
    //数据增量同步；判断目标表的最大id值；以目标表的id值从源表开始同步
    private $pkey;
    private  $tbname1;
    private $tbname2;
    private function aysndata()
    {
        $pkeys= $this->db1->gettableinfo($this->table);
        $this->pkey=$pkeys[0]['Field'];
        $pkey=$this->pkey;
        // d($this->db2);
        $maxId1 = $this->getMaxId($this->db1, $this->table,$pkey);
        $maxId2 = $this->getMaxId($this->db2, $this->table,$pkey);
        
        if($maxId1<=$maxId2){
            // d($this->table."数据量一样不用同步");
            return false;
        }
        d($this->table."同步数据");
        $prefix = $this->db2->getpre();
        $table=$prefix.$this->table;
        $this->loopinsert($maxId2);
        // 开启事务
        // $this->db2->beginTransaction();
        // try {
        //     // 插入数据到目标表
        //     foreach ($sourceData as $row) {
        //         // 构建并执行插入语句
        //         $placeholders = implode(', ', array_fill(0, count($row), '?'));
        //         $stmt = $this->db2->exec("INSERT INTO `{$table}` VALUES ({$placeholders})");
        //         // $stmt->execute(array_values($row));
        //     }
        //     // 提交事务
        //     // $this->db2->commit();
        // } catch (Exception $e) {
        //     // 回滚事务
        //     // $this->db2->rollBack();
        //     throw $e;
        // }
        // // 更新目标表的最大 ID，以便下次同步
        // $this->updateMaxId($this->db2, $this->table, $maxId);
    }
    //开始循环插入；
    private function loopinsert($sid){
        $list=$this->getSourceData($this->db1,$this->table,$sid,$this->batchSize,$this->pkey);
      
        $num=sizeof($list);
        if($num){
        $lastmaxid=$list[$num-1][$this->pkey];//从最后一条数据id开始；因为id可能不连续所以必须取最后一条
        $sql="";
        
        foreach ($list as $key => $row) {
            // $sql.= implode(', ', $row).",";  
            $r="";
            foreach ($row as $key2 => $value) {
                $r.='"'.$value.'",';
            }
           $r= substr($r, 0, -1);
            $sql.="({$r}),"; 
        }
        
            $sql= substr($sql, 0, -1);
         $this->addsql($sql);
        $this->loopinsert($lastmaxid);
        }else{
           d($this->table."同步完成"); 
        }
    }
    private function addsql( $sql)
    {
      
        $table=$this->db2->getpre().$this->table;
        // $sql=" INSERT DELAYED INTO {$table} (column1, column2, ...)
        $sql=" INSERT  INTO {$table} 
            VALUES {$sql}";
        //  d($sql,1);
         $this->db2->exec($sql);
        // 执行更新操作，设置目标表的最大 ID 为当前最新同步的 ID
        // 这里需要根据实际的业务逻辑来确定如何更新最大 ID
    }
    private function getSourceData($dao, $table, $maxId=0, $batchSize,$pkey)
    {
        // 构建并执行查询以获取需要同步的数据
      
        $prefix = $dao->getpre(); // 假设 getpre 方法返回数据库表前缀
        $sql="SELECT * FROM `{$prefix}{$table}` WHERE `{$pkey}` >  $maxId  "."order by `{$pkey}` asc limit " .$batchSize  ;
         //顺序必须是主键上升
        $stmt = $dao->getall($sql);
      
        return $stmt;
        // $stmt->execute([$maxId]);
        // $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // 分批处理以支持大表数据同步
        // foreach (array_chunk($results, $batchSize) as $batch) {
        //     yield $batch;
        // }
    }
    private function getMaxId($dao, $table,$pkey)
    {
        $wztb = $dao->getpre().$table;
       
        $sql="SELECT MAX({$pkey}) as id FROM {$wztb}";
        $stmt = $dao->getone($sql);
       
        if(!$stmt['id'])return 0;
        return $stmt['id'];
    }
}

// 使用示例
$synchronizer = new dbcp('main', 'other');
$synchronizer->synchronize();
