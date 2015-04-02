<?php
/*
    备份的目录结构
// BACKUP_DIR>/<host>/database/<daily|weekly|monthly>/<database>_YYYY-MM-DD.sql[.<COMPRESSION_EXTENSION>
*/
/*
    数据的保存份数策略
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
        $today_file  = '';
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
            if (! file_exists($filepath . '.gz')) {
              createDir(dirname($filepath));
            }else{
              continue;
            }
            echo $info = shell_exec("mysqldump -h $host -u $user -p$password $db_name > $filepath");
            if ( file_exists($filepath)) {
              echo $info = shell_exec("gzip $filepath");
            }
            echo $filepath. "\n";
          }
          echo $db_name. "\n";
        }

