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

$lokiDb
    ->createQuery()
    ->insert([
        'user_name' => 'karl123',
        'email' => 'karl@domain.tld',
        'age' => 19,
        'is_active' => 1,
    ])
    ->into('users')
    ->execute();

$lokiDb->commit();






