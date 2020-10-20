<?php

use LokiDb\Db;
use LokiDb\Query\Condition;
use LokiDb\Storage\FieldDefinition;
use LokiDb\Storage\TableDefinition;

require_once __DIR__ . '/../vendor/autoload.php';


Condition::setSymbols([
    'sd' => 9
]);


$c = new Condition(
    'sd',
    '+',
    new Condition(
        6,
        '-',
        1
    )
);






var_dump($c->solve());






die();


$lokiDb = new Db(__DIR__ . '/test_db');

$table = new TableDefinition(
    'users',
    []
);

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



//$lokiDb->beginTransaction();
/*
for($i = 0; $i < 1000000; $i++)
{
    $lokiDb
        ->createQuery()
        ->insert([
            'user_name' => 'janet',
            'email' => 'janet@domain.tld',
            'age' => rand(0, 100),
            'is_active' => false,
        ])
        ->into('users')
        ->execute();
}
*/


foreach ($lokiDb->createQuery()->select()->from('users')->where('age = 10')->execute() as $row)
{

}








//$lokiDb->commit();



