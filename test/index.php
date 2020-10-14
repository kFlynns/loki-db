<?php

use LokiDb\Db;
use LokiDb\Storage\FieldDefinition;
use LokiDb\Storage\TableDefinition;

require_once __DIR__ . '/../vendor/autoload.php';


$lokiDb = new Db(__DIR__ . '/test_db');

$table = new TableDefinition('users', []);
$table->addField(
        'user_name',
        FieldDefinition::DATA_TYPE_STRING,
        16
    )->addField(
        'email',
        FieldDefinition::DATA_TYPE_STRING,
        64
    )->addField(
        'age',
        FieldDefinition::DATA_TYPE_INT
    )->addField(
        'is_active',
        FieldDefinition::DATA_TYPE_BOOL
    );

$lokiDb->createTable($table);


$lokiDb->beginTransaction();

//$lokiDb->createQuery()->insert(['user_name' => 'asd'])->into('users')->execute();


$result = $lokiDb
    ->createQuery()
    ->select()
    ->from('users')
    ->execute();

print_r($result);

/*
$lokiDb
    ->createQuery()
    ->insert([
        'user_name' => 'janet',
        'email' => 'janet@domain.tld',
        'age' => 29,
        'is_active' => 0,
    ])
    ->into('users')
    ->execute();
*/


$lokiDb->commit();






