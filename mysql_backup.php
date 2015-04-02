<?php

/*
    备份的目录结构
// BACKUP_DIR>/<host>/database/<daily|weekly|monthly>/<database>_YYYY-MM-DD.sql[.<COMPRESSION_EXTENSION>
*/
/*
    数据的保存份数策略
    同时为了保证数据量，会清一些比较久的数据
//每月每周每天都会备份数据
// 删除2天前的数据
// 删除2周前的数据
// 删除2月前的数据
*/
set_time_limit(0);
ini_set('memory_limit', '-1');
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

function backup_init(){
  // file_put_contents($log_file, '', FILE_APPEND);
}

backup_init();

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
               $info = shell_exec("cp $today_file $filepath.gz");
              file_put_contents($log_file,  date('Y-m-d H:i:s') . ':  file, ' . $filepath . 'was generated , and gziped' ."\n", FILE_APPEND);
              continue;
            }else{ // 否则备份
              $info = shell_exec("mysqldump -h $host -u $user -p'$password' $db_name > $filepath");
              file_put_contents($log_file,  date('Y-m-d H:i:s') . ':  file, ' . $filepath . 'was generated ' ."\n", FILE_APPEND);
            }
            // 备份成功，则把备份压缩存储
            if ( file_exists($filepath)) {
              $info = shell_exec("gzip $filepath");
              file_put_contents($log_file,  date('Y-m-d H:i:s') . ':  file, ' . $filepath . 'was gziped ' ."\n", FILE_APPEND);
              //   备份好后，记录下己经dump过了，后面可以直接使用这个文件
              if (! isset($today_file) && file_exists($filepath . '.gz')) {
                        $today_file  = $filepath . '.gz';
              }
            }
            // echo $filepath. "\n";
            // file_put_contents($log_file,  date('Y-m-d H:i:s') . ':  file, ' $filepath . 'was generated ' ."\n", FILE_APPEND);
          }
          // echo $db_name. "\n";
          file_put_contents($log_file,  date('Y-m-d H:i:s') . ':  db, ' . $db_name . '  was scanned ' ."\n", FILE_APPEND);
        }

// 删除过长时间的备份
foreach ($databases as $db_name) {
      foreach ($backup_frequency as $frequency) {
          switch ($frequency) {
              case 'daily':
              $time_prex = 'daily';
              $remain = $daily_remain;
              break;
              
              case 'weekly':
              $time_prex = 'weekly';
              $remain = $weekly_remain;
              break;
              
              case 'monthly':
              $time_prex = 'monthly';
              $remain = $monthly_remain;
                break;
              
              default:
                exit;
                break;
            }

            $filepattern = $backup_dir . $host . '/' . $db_name . '/' . $time_prex . '/*' ;
           $count = glob($filepattern);
           for ($i=0; $i < $remain; $i++) { 
            array_pop($count);
           }
           if (count($count) > 0) {
            foreach ($count as $file_rm) {
              unlink($file_rm);
              file_put_contents($log_file,  date('Y-m-d H:i:s') . ':  file, '  .$file_rm . 'was removed ' ."\n", FILE_APPEND);
             }
           }
      }
    }
