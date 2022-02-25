<?php

namespace KFlynns\Test;

use KFlynns\LokiDb\Db;
use KFlynns\LokiDb\Storage\FieldDefinition;
use KFlynns\LokiDb\Storage\Schema;
use KFlynns\LokiDb\Storage\TableDefinition;
use PHPUnit\Framework\TestCase;

class DbTest extends TestCase
{

    public function testCreateNonExistingSchema(): void
    {
        $this->expectDeprecationMessageMatches('/^There must be a readable schema file under: "([a-z0-9\/\-\\\.\s]+)\/NON_EXISTENT\/loki.json".$/i');
        new Schema(__DIR__ . '/NON_EXISTENT');
    }

    public function testCreateSimpleSchema(): void
    {
        $schema = new Schema(__DIR__ . '/data/test_schema-creation');
        $db = new Db($schema);

        $this->assertMatchesRegularExpression(
            '/\/test\/data\/test_schema-creation$/',
            $db->getSchema()->getDatabaseFolder()
        );

        /** @var TableDefinition $table */
        $table = $db->getSchema()->getTables()['users'];
        $this->assertEquals('users', $table->getName());

        /** @var FieldDefinition $field */
        $field = $table->getFieldDefinitions()[0];
        $this->assertEquals('name', $field->getName());
        $this->assertEquals('string', $field->getDataType());
        $this->assertEquals(128, $field->getByteLength());
    }

}