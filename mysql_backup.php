<?php
/*
    备份的目录结构
// BACKUP_DIR>/<host>/database/<daily|weekly|monthly>/<database>_YYYY-MM-DD.sql[.<COMPRESSION_EXTENSION>
*/
/*
    数据的保存份数策略
    同时为了保证数据量，会清一些比较久的数据
//每月每周每天都会备份数据
// 删除3天前的数据
// 删除2周前的数据
// 删除1月前的数据
*/
require 'config.php';
/*
 * 功能：循环检测并创建文件夹
 * 参数：$path 文件夹路径
 * 返回：
 */
function createDir($path){
  if (!file_exists($path)){
    createDir(dirname($path));
    mkdir($path);
  }
}

foreach ($databases as $db_name) {
      if (isset($today_file)) {
        unset($today_file); // 重置己经备份的文件
      }
      foreach ($backup_frequency as $frequency) {
            switch ($frequency) {
              case 'daily':
              $time = date('Y-m-d');
              $time_prex = 'daily';
              break;
              
              case 'weekly':
              $time = date('W-Y');
              $time_prex = 'weekly';
              break;
              
              case 'monthly':
              $time = date('Y-m');
              $time_prex = 'monthly';

                break;
              
              default:
                exit;
                break;
            }
            $filepath = $backup_dir . $host . '/' . $db_name . '/' . $time_prex . '/'  . $db_name . '_' . $time . '.sql';
            // 如果不存在，则自动创建相关目录
            if (! file_exists($filepath . '.gz')) {
              createDir(dirname($filepath));
            }else{
              // 存在则自动跳出循环
              continue;
            }
            // 这个数据库刚才是否备份过，如果备份过，则复制一份，然后退出循环
            if (isset($today_file)) {
              echo $info = shell_exec("cp $today_file $filepath.gz");
              continue;
            }else{ // 否则备份
              echo $info = shell_exec("mysqldump -h $host -u $user -p$password $db_name > $filepath");
            }
            // 备份成功，则把备份压缩存储
            if ( file_exists($filepath)) {
              echo $info = shell_exec("gzip $filepath");
              //   备份好后，记录下己经dump过了，后面可以直接使用这个文件
              if (! isset($today_file) && file_exists($filepath . '.gz')) {
                        $today_file  = $filepath . '.gz';
              }
            }
            echo $filepath. "\n";
          }
          echo $db_name. "\n";
        }

