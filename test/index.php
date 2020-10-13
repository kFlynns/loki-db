<?php

use LokiDb\Db;
use LokiDb\Table\FieldDefinition;

require_once __DIR__ . '/../vendor/autoload.php';


$lokiDb = new Db(__DIR__ . '/test_db');


$lokiDb->createTable('users', [
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


$lokiDb->beginTransaction();

$lokiDb
    ->createQuery()
    ->select(['user_name', 'email'])
    ->from('users')
    ->execute();


$lokiDb->commit();