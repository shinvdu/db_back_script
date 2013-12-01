#!/bin/bash 

SQL_name='xxxx xxx xxx'

SQL_pwd='xxxxxx'                        #数据库密码； 

#SQL_path='/usr/local/mysql/bin'        #数据库命令目录； 

# BACKUP_tmp=/data/backup/tmp     #备份文件临时存放目录； 

BACKUP_path=/root/database           #备份文件压缩打包存放目录； 

for i in $SQL_name
  do
    mysqldump -u root -p$SQL_pwd $i > $BACKUP_path/$i-$(date +%y-%m-%d-%H-%M).sql
  sleep 3
done

cd $BACKUP_path
for j in `ls`
  do
    tar -zcvf $j.tar.gz $j
   rm -f $j
done
exit 0
