<?php

use ng169\cli\Clibase;
use ng169\lib\Option;

require_once "../clibase.php";
class mysqlfenqu extends Clibase
{
    private $pdo;
    private $dbqz;
    function initpdo()
    {
        $dbqz = DB_PREFIX;
        $dbconf = 'main';
        $dbs = Option::get('db');
        if (isset($dbs[$dbconf])) {
            $conf = $dbs[$dbconf];
            $dbqz = $conf['dbpre'];
            $this->dbqz = $dbqz;
        } else {
            error(__('数据库配置不存在'));
        }
        try {
            $this->pdo = new PDO("mysql:host=" . $conf['dbhost'] . ";dbname=" . $conf['dbname'] . "", $conf['dbuser'], $conf['dbpwd']);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            // 对指定的表进行分区检查
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
    }
    function needfq($tableName)
    {
        $sizeQuery = "SELECT SUM(DATA_LENGTH + INDEX_LENGTH) AS total_size FROM information_schema.PARTITIONS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :tableName";
        // 准备并执行查询
        $stmt = $this->pdo->prepare($sizeQuery);
        $stmt->bindParam(':tableName', $tableName);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        // 获取表的总大小（以字节为单位）
        $totalSize = $result ? $result['total_size'] : 0;
        // 定义一个阈值，超过该阈值就进行分区（例如，200MB）
        $threshold = 200 * 1024 * 1024; // 200MB
        // 将字节转换为MB以便于阅读
        $totalSizeMB = $totalSize / (1024 * 1024);
        d("表大小$totalSizeMB MB");
        if ($totalSize > $threshold) {
            return $totalSize;
        } else {
            return false;
        }
    }
    function startfq($tableName,$tableSize)
    {
        $primaryKeyStmt = $this->pdo->query("SHOW KEYS FROM `$tableName` WHERE Key_name = 'PRIMARY'");
        $primaryKeyResult = $primaryKeyStmt->fetch(PDO::FETCH_ASSOC);
        $primaryKey = $primaryKeyResult ? $primaryKeyResult['Column_name'] : null;
        
        // 如果没有找到主键，则返回
        if (!$primaryKey) {
            echo "Table '$tableName' does not have a primary key.\n";
            return;
        }
        
        // 查询主键的最大值和最小值，用于估算分区数
        $rangeStmt = $this->pdo->query("SELECT MIN($primaryKey), MAX($primaryKey) FROM `$tableName`");
        $rangeResult = $rangeStmt->fetch(PDO::FETCH_ASSOC);
        $minValue = $rangeResult['MIN(' . $primaryKey . ')'];
        $maxValue = $rangeResult['MAX(' . $primaryKey . ')'];
        
        // 根据主键的范围估算分区数，这里简单地假设每个分区大约有 10000 个键值
        // $partitionsNum = (int)(($maxValue - $minValue) / 10000) + 1;
        // 定义分区大小阈值（例如，每个分区 100MB）
        $partitionSizeThreshold = 400 * 1024 * 1024;
         // 估算分区数，假设每个分区大约 100MB
         $partitionsNum = (int)($tableSize / $partitionSizeThreshold);

    // 确保至少有一个分区
        $partitionsNum = max(1, $partitionsNum);
        // 构建分区 SQL 语句
        $alterTableSql = "ALTER TABLE `$tableName` PARTITION BY HASH ($primaryKey) PARTITIONS $partitionsNum;";
    
        // 执行分区 SQL 语句
        try {
            $this->pdo->exec($alterTableSql);
            echo "表 '$tableName' 已经被自动分割成 $partitionsNum 个分区.\n";
        } catch (PDOException $e) {
            echo "Error partitioning table '$tableName': " . $e->getMessage() . "\n";
        }
    //    时间分区
        // // 假设我们决定根据表中的时间戳列 `date_column` 进行分区
        // // 这里需要根据实际情况来设计分区策略，以下是一个简单的示例
        // $key = 'date_column';
        // // 获取表中的最大和最小日期值，以确定分区的范围
        // $dateRangeStmt = $this->pdo->query("SELECT MIN($key), MAX($key) FROM `$tableName`");
        // $dateRangeResult = $dateRangeStmt->fetch(PDO::FETCH_ASSOC);
        // $minDate = $dateRangeResult["MIN($key)"];
        // $maxDate = $dateRangeResult["MIN($key)"];

        // // 计算需要多少个分区
        // // 这里我们简单地根据日期范围和当前日期来估算分区数量
        // $dateInterval = strtotime($maxDate) - strtotime($minDate);
        // $partitionsNum = ceil($dateInterval / (365 * 24 * 60 * 60)) + 1; // 每年至少一个分区

        // // 构建分区 SQL 语句
        // $alterTableSql = "ALTER TABLE `$tableName` PARTITION BY RANGE (TO_DAYS($key)) (
        //     ";
        // for ($i = 0; $i < $partitionsNum; $i++) {
        //     $partitionDate = date('Y-m-d', strtotime("-$i year", strtotime($maxDate)));
        //     $alterTableSql .= "PARTITION p$i VALUES LESS THAN (TO_DAYS('$partitionDate')),
        //     ";
        // }
        // // 删除最后一个多余的逗号
        // $alterTableSql = rtrim($alterTableSql, ',');
        // $alterTableSql .= ");";
        // d($alterTableSql, 1);
        // // 执行分区 SQL 语句
        // $this->pdo->exec($alterTableSql);
    }
    function fq($tableName = null)
    {
        if (!$tableName) {
            $tb = $this->getargv(['table']);
            $tableName = $tb['table'];
            if (!$tableName) {
                d("请输入你要自动分区的表", 1);
            }
        }
        $this->initpdo();
        $tableName = $this->dbqz . $tableName;
       $tsize=$this->needfq($tableName);
        if ($tsize) {
            $this->startfq($tableName,$tsize);
        } else {
            d("你选择的表无须分区", 1);
        }


        // 检查表的行数
        // $pdo = $this->pdo;
        // $stmt = $pdo->prepare("SELECT COUNT(*) FROM `$tableName`");
        // $stmt->execute();
        // $rowCount = $stmt->fetchColumn();

        // // 定义一个阈值，超过该阈值就进行分区
        // $threshold = 100000; // 例如，超过100万行进行分区

        // if ($rowCount > $threshold) {
        //     // 假设我们根据日期列进行分区，并且表中有一个名为 `date_column` 的日期类型列
        //     // 这里只是一个简单的示例，实际应用中需要根据数据的实际情况来设计分区策略
        //     $partitions = ceil($rowCount / $threshold); // 计算分区数量

        //     // 构建分区 SQL 语句
        //     $partitionSql = "ALTER TABLE `$tableName` PARTITION BY RANGE (YEAR(date_column)) (
        //         ";
        //     for ($i = 0; $i < $partitions; $i++) {
        //         $partitionSql .= "PARTITION p$i VALUES LESS THAN (" . ($i + 1) . "),";
        //     }
        //     // 删除最后一个多余的逗号
        //     $partitionSql = rtrim($partitionSql, ',');
        //     $partitionSql .= ")";

        //     // 执行分区 SQL 语句
        //     $stmt = $pdo->prepare($partitionSql);
        //     $stmt->execute();

        //     echo "Table '$tableName' has been partitioned into $partitions partitions.\n";
        // } else {
        //     echo "Table '$tableName' does not need partitioning.\n";
        // }
    }
    public function help()
    {
        d("支持输入参数table;如 mysqlfg.php table=cartoon", 1);
    }
}
// 使用示例
$fq = new mysqlfenqu();
$fq->fq();
