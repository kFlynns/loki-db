<?php

namespace KFlynns\Test\Query;
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


    public function testCreateSelectQuery(): void
    {
        $db = $this->environment->getTempDatabase([
            'test' => [
                'field' => [
                    'type' => 'string',
                    'length' => 16
                ]
            ]
        ]);
    }



}