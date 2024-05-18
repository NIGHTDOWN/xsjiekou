<?php

namespace ng169\db;

use \ng169\Y;
use \PDO;

class Dbsql
{
    public $querynum = 0;
    public $link;
    public $charset;
    public function __construct($dbhost, $dbuser, $dbpw, $dbname = '', $dbcharset =
    'utf8', $pconnect = 1, $halt = true)
    {
        $dsn = G_DB_TYPE . ":host=$dbhost;dbname=$dbname;charset=$dbcharset";
        try {
            /* $this->link = new PDO($dsn, $dbuser, $dbpw,
            array(PDO::ATTR_PERSISTENT=> true)); */
           
            $this->link = new PDO($dsn, $dbuser, $dbpw, array(
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_TIMEOUT => 3,
                PDO::ATTR_PERSISTENT=> true
            ));
            if (PHP_SAPI == 'cli') {
              
                $query = $this->link->prepare("set session wait_timeout=31536000,interactive_timeout=31536000,net_read_timeout=10000");
                $query->execute();
            }
        } catch (\Exception $e) {
            error("Unable to connect: " . $e->getMessage());
        }
    }
    /**
     * 执行查询
     * @param string $sql
     * @return data
     */
    public function query($sql, $cache = false, $time = 0)
    {
        try {
            if ($cache) {
                $index = md5($sql);
                $cacheob = &Y::$cache;
                list($bool, $data) = $cacheob->get($index);
                if ($bool) {
                  
                    return $data;
                }
            }

            $this->link->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            try {

                $pdostream = $this->link->query($sql);
            } catch (\Error $e) {
                error($e->getMessage() . '【' . $sql . '】');
                /*error($e);*/
            } catch (\Exception $e) {
                error($e->getMessage() . '【' . $sql . '】');
                /*error($e);*/
            }
            $data = $pdostream->fetchAll(PDO::FETCH_ASSOC);
            if ($cache) {
                $cacheob->set($index, $data);
            }
           
        } catch (\Exception $e) {
            error($e->getMessage() . '【' . $sql . '】');
        }
        return $data;
    }
    /**
     * 执行查询
     * @param string $sql
     *
     * @return data
     */
    public function getone($sql, $cache = false, $time = 0)
    {

        try {
            if ($cache) {
                $index = md5($sql);
                $cacheob = &Y::$cache;
                list($bool, $data) = $cacheob->get($index);
                if ($bool) {
                    return $data;
                }
            }
            $this->link->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            //      $this->link->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, 3);

            $pdostream = $this->link->query($sql);

            $pdostream->setFetchMode(PDO::FETCH_ASSOC);

            $data = $pdostream->fetch();
            // d($sql);
            // d($data);
            if ($cache) {
                //设置缓存
                $cacheob->set($index, $data);
            }
          
        } catch (\Exception $e) {

            error($e->getMessage() . '【' . $sql . '】');
        }

        return $data;
    }
    /**
     * 执行删
     * @param string $sql
     *
     * @return int row受影响的行数
     */
    public function exec($sql)
    {
      
        //  $sql='insert tp_n_share set type=1';
        //  $this->starttransaction();
        //  try {
        //    $this->link->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        //    $row = $this->link->exec($sql);
        //    /*if($row)*/
        //    // 提交事务
        //    $this->commit();
        //  } catch (\Exception $e) {
        //    $this->rollback();
        //    /*throw $e;*/
        //   error($e->getMessage().'【'.$sql.'】');
        //  }
        $this->link->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $row = $this->link->exec($sql);
      
        return $row;
    }
    /**
     * 执行增
     * @param string $sql
     *
     * @return int 插入的主键ID
     */
    public function insert($sql)
    {


        // $this->starttransaction();
        // try {
        //     $this->link->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        //     $row = $this->link->exec($sql);
        //     $id = $this->link->lastInsertId();
        //     /*if($row)*/
        //     // 提交事务
        //     $this->commit();
        // } catch (\Exception $e) {
        //     $this->rollback();
        //     /*throw $e;*/
        //     error($e->getMessage() . '【' . $sql . '】');
        // }
        $row = $this->link->exec($sql);
        $id = $this->link->lastInsertId();
        return $id;
    }
    /**
     * 开启事务
     *
     * @return void
     */
    public function starttransaction()
    {
        return $this->link->beginTransaction();
    }
    /**
     * 提交事务
     *
     * @return void
     */
    public function commit()
    {
        return $this->link->commit();
    }
    /**
     * 回滚事务
     *
     * @return void
     */
    public function rollback()
    {
        return $this->link->rollback();
    }
}
