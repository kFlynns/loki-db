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


    public function testSelectFromNotExistingTable(): void
    {
        $db = $this->environment->getTempDatabase([
            'table' => [
                'field' => [
                    'type' => 'string',
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

}