<?php
/**
 *链接给数据库并返回数据库链接句柄
 *
 */
$pdo = new PDO('mysql:host=localhost;dbname=api','root','root');
$pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES,false);
return $pdo;
