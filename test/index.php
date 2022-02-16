<?php

use KFlynns\LokiDb\Db;
use KFlynns\LokiDb\Storage\Schema;

require_once __DIR__ . '/../vendor/autoload.php';

$schema = new Schema(__DIR__ . '/data/test_schema-creation');
$db = new Db($schema);

for($i = 0; $i < 1; $i++)
{
    $db
        ->createQuery()
        ->insert([
            'name' => 'janet',
            'email' => 'janet@domain.tld',
            'age' => rand(0, 100),
            //'is_active' => false,
        ])
        ->into('uses')
        ->execute();
}


/*
$db
    ->createQuery()
    ->update('users')
    ->set(['name' => 'karl'])
    ->where(new Condition('age', '=', 1000))
    ->execute();
*/

/*
$lokiDb = new Db(__DIR__ . '/data');

$table = new TableDefinition(
    'users', []
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

//$lokiDb->createTable($table);

//$lokiDb->beginTransaction();


/*

*/


/*



//$lokiDb->createQuery()->delete()->from('users')->where(new Condition('age','>', 90))->execute();



foreach (
    $lokiDb
        ->createQuery()
        ->select()
        ->from('users')
        ->where(
            new Condition('age','>', 98)
        )
        ->execute() as $row
) {
    print_r($row);
}









//$lokiDb->commit();


*/
