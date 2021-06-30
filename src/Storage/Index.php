<?php

namespace LokiDb\Storage;

use GuzzleHttp\Psr7\Stream;
use LokiDb\Btree;
use LokiDb\Exception\RunTimeException;

/**
 * Class Index
 * @package LokiDb\Storage
 */
class Index
{

    const SORT_ASC = 0x0;

    const SORT_DESC = 0x1;

    /** @var ITable */
    private $table;

    /** @var IField */
    private $field;

    /** @var bool */
    private $unique;

    /** @var int */
    private $sort;

    /** @var Stream */
    private $leftStream;

    /** @var Stream */
    private $rightStream;

    /** @var Btree */
    protected $bTree;


    /**
     * Index constructor.
     * @param string $databaseFolder,
     * @param ITable $table,
     * @param FieldDefinition $fieldDefinition
     * @param bool $unique
     * @param int $sort
     */
    public function __construct(
        $databaseFolder,
        ITable $table,
        IField $field,
        $unique = false,
        $sort = self::SORT_ASC
    ) {

        $fileName = rtrim($databaseFolder, '/\\') . '/' . implode(
            '/',
            str_split(
                $table->getUId(),
                2
            )
        ) . '/lki_' . $field->getName() . '-idx.';

        foreach (['left', 'right'] as $extension)
        {
            $path = $fileName . $extension;
            if(!is_writable($path))
            {
                touch($path);
                chmod($path, 0600);
            }
            if(!is_writable($path))
            {
                throw new RunTimeException('Could not write index file under: "' . $path . '".');
            }
            $this->{$extension . 'Stream'} = new Stream(fopen($path, 'r+'));
        }


        //$this->bTree = new Btree();
        $this->table = $table;
        $this->field = $field;
        $this->unique = $unique;
        $this->sort = $sort;






        //$this->generateNew();

    }




    public function tryToLoadFromDisk()
    {
        $this->leftStream->rewind();

    }




    /**
     *
     */
    public function generateNew()
    {
        $data = [];
        foreach ($this->table->fetch() as $row)
        {
            $value = $row[$this->field->getName()] ?? false;
            if($value)
            {
                $data[] = $value;
            }
        }
        $this->bTree = new Btree($data);
    }


    /**
     * write index to disk
     * @throws \Exception
     */
    public function __destruct()
    {
        if($this->leftStream)
        {
            $this->leftStream->rewind();
            $this->leftStream->write(
                $this->bTree->pack('left')
            );
            $this->leftStream->close();
        }
        if($this->rightStream)
        {
            $this->rightStream->rewind();
            $this->rightStream->write(
                $this->bTree->pack('right')
            );
            $this->rightStream->close();
        }
    }


}