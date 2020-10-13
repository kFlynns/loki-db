<?php

use LokiDb\Db;
use LokiDb\Table\FieldDefinition;
use LokiDb\Table\Table;

require_once __DIR__ . '/../vendor/autoload.php';

$lokiDb = new Db(__DIR__ . '/test_db');
$table = new Table('users');

$table->addDefinition([
    new FieldDefinition(
        'user_name',
        FieldDefinition::DATA_TYPE_STRING,
        16
    ),
    new FieldDefinition(
        'email',
        FieldDefinition::DATA_TYPE_STRING,
        128
    ),
    new FieldDefinition(
        'age',
        FieldDefinition::DATA_TYPE_INT
    ),
    new FieldDefinition(
        'is_active',
        FieldDefinition::DATA_TYPE_BOOL
    )
]);
$lokiDb->registerTable($table);




$lokiDb->beginTransaction();


$lokiDb->insert('users', [
    'user_name' => 'testuser',
    'email' => 'testuser@domain.tld',
    'age' => 18,
    'is_active' => true
]);


$lokiDb->commit();