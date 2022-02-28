<?php

namespace KFlynns\Test\Query;
use KFlynns\LokiDb\Storage\FieldDefinition;
use KFlynns\Test\Environment;
use PHPUnit\Framework\TestCase;

class QueryTest extends TestCase
{
    /** @var Environment  */
    private $environment;

    public function setUp(): void
    {
        $this->environment = new Environment($this);
    }

    public function tearDown(): void
    {
        $this->environment = null;
    }


    public function testInsertIntoNotExistingTable(): void
    {
        $db = $this->environment->getTempDatabase([
            'table' => [
                'field' => [
                    'type' => FieldDefinition::DATA_TYPE_STRING,
                    'length' => 16
                ]
            ]
        ]);
        $this->expectExceptionMessage('The table "NON_EXISTENT" is unknown.');
        $db
            ->createQuery()
            ->insert(['field' => ''])
            ->into('NON_EXISTENT')
            ->execute();
    }

    public function testInsertIntoNotExistingFields(): void
    {
        $db = $this->environment->getTempDatabase([
            'table' => [
                'field' => [
                    'type' => FieldDefinition::DATA_TYPE_STRING,
                    'length' => 16
                ]
            ]
        ]);
        $this->expectExceptionMessage('The field "NON_EXISTENT" is unknown.');
        $db
            ->createQuery()
            ->insert(['NON_EXISTENT' => 'sd'])
            ->into('table')
            ->execute();
    }

    public function testInsertIntoNoFields(): void
    {
        $db = $this->environment->getTempDatabase([
            'table' => [
                'field' => [
                    'type' => FieldDefinition::DATA_TYPE_STRING,
                    'length' => 16
                ]
            ]
        ]);
        $this->expectExceptionMessage('For inserting or updating a table, there must be specified fields.');
        $db
            ->createQuery()
            ->insert([])
            ->into('table')
            ->execute();
    }

    public function testSelectFromNotExistingTable(): void
    {
        $db = $this->environment->getTempDatabase([
            'table' => [
                'field' => [
                    'type' => FieldDefinition::DATA_TYPE_STRING,
                    'length' => 16
                ]
            ]
        ]);
        $this->expectExceptionMessage('The table "NON_EXISTENT" is unknown.');
        $db
            ->createQuery()
            ->select(['field'])
            ->from('NON_EXISTENT')
            ->execute();
    }



    public function testSingleInsertAndSelect(): void
    {
        $db = $this->environment->getTempDatabase([
            'table' => [
                'char' => [
                    'type' => 'char'
                ],
                'string' => [
                    'type' => FieldDefinition::DATA_TYPE_STRING,
                    'length' => 16
                ],
                'integer' => [
                    'type' => FieldDefinition::DATA_TYPE_INT,
                    'length' => 16
                ],
                'boolean' => [
                    'type' => FieldDefinition::DATA_TYPE_BOOL
                ],
                // todo
            ]
        ]);



        $db
            ->createQuery()
            ->insert([
                'char' => 300, // overflow -> test if 300 mod 256 == 44
                'string' => 'test value $%&/',
                'integer' => -100,
                'boolean' => true
            ])
            ->into('table')
            ->execute();

        $this->assertEquals([
            'char' => 44,
            'string' => 'test value $%&/',
            'integer' => -100,
            'boolean' => true
        ], $db
            ->createQuery()
            ->select()
            ->from('table')
            ->execute()
            ->current()
        );



    }

}