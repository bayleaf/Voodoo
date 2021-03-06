;<?php exit(); // Always exit out, if file is called explicitely ?>
; NOTE: *.conf.php is an INI file
;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;
;
; VoodooPHP - _conf/DB.ini.php
;
; This INI file contains your database configuration which can be access by alias name
; By having alias name, you get to hold multiple database configuration.
; For your convenience, the class Voodoo\Core\ConnectionManager manages the connection
; by providing the AliasName
;
; Options
;
;[AliasName] 
;    ; mysql|pgsql|sqlite|dsn
;    type       = "mysql"
;
;    ; MySQL, PGSQL
;    host       = "localhost" 
;    port       = 3306
;    user       = "root" 
;    password   = "" 
;    dbname     = "" 
;
;    ; DSN : Data source name for type = sqlite or dsn. 
;    ; If type=sqlite, Enter the full path to the file
;    ; If type=dsn, enter the source of data source ie: IP:PORT
;    dsn        = "" 
;
;    ; DSN CLIENT
;    ; A class the dsn connection depends on to create the instance.
;    ; ie: \Redisent\Redis or \MongoClient()
;    dsnClient = "" 
;
;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;
; Examples
; You can add as many alias as you want
;[AliasName]
;   type        = "mysql" (options: mysql | pgsql)
;   host        = "localhost"
;   port        = 3306
;   user        = "root"
;   password    = ""
;   dbname      = "your-db-name"
;
;[AnotherAliasUsingSQLite]
;   type        = "sqlite"
;   dsn      = "path/my/db.sqlite"
; 
;[MyRedis]
;   type        = "dsn"
;   dsn        = "ip:6379"
;   dsnClient = "\Redisent\Redis"
;
;
; To access data from this file use the Config class as so:
; Voodoo\Core\Config::DB()->get($dotNotation);
; where $dotNotation is a string with dot notation to access data, like: 'MyDB.dbname'
;
; i.e: $dbname = Voodoo\Core\Config::DB()->get("MyDB.dbname");
;
;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;

; The default DB settings
[MyDB] 
    ; mysql|pgsql|sqlite|dsn
    type       = "mysql"

    ; MySQL, PGSQL
    host       = "localhost" 
    port       = 3306
    user       = "root" 
    password   = "" 
    dbname     = "" 

    ; DSN : Data source name for type = sqlite or dsn. 
    ; If type=sqlite, Enter the full path to the file
    ; If type=dsn, enter the source of data source ie: IP:PORT
    dsn        = "" 

    ; DSN CLIENT
    ; A class the dsn connection depends on to create the instance.
    ; ie: \Redisent\Redis or \MongoClient()
    dsnClient = "" 


 