<?php

namespace LokiDb\Storage;

use GuzzleHttp\Psr7\Stream;

/**
 * Class Table
 * @package LokiDb\Table
 */
class Table implements ITable
{

    /** @var Stream */
    private $stream;

    /** @var resource */
    private $fileResource;

    /** @var array[Field]  */
    private $fields = [];

    /** @var string */
    private $name;

    /** @var int */
    private $rowLength = 0;

    /** @var string */
    private $packDescriptor;

    /** @var string */
    private $unpackDescriptor;

    /** @var string */
    private $dataRow;

    /** @var string */
    private $uId;

    /** @var string */
    private $diskFilePath;

    /** @var int */
    private $datasetPointer;

    /** @var array */
    private $journal = [];

    /**
     * Table constructor.
     */
    private function __construct() {}


    /**
     * @param string $tableName
     * @return ITable
     */
    static public function create($tableName) : ITable
    {
        $table = new self();
        $table->name = $tableName;
        $table->uId = hash('md5', $table->name);
        $table->diskFilePath = implode(
                '/',
                str_split(
                    $table->uId,
                    2
                )
            ) . '/lki';
        return $table;
    }

    /**
     *
     */
    public function __destruct()
    {
        $this->stream->close();
    }

    /**
     * @param array[array] $data
     */
    public function setDataRow(array $data) : void
    {
        $sortedRow = [];

        /** @var IField $field */
        foreach ($this->fields as $field)
        {
            if(isset($data[$field->getName()]))
            {
                $sortedRow[] = $data[$field->getName()];
                continue;
            }
            $sortedRow[] = null;
        }

        $this->dataRow = call_user_func_array (
            'pack',
            array_merge([
                    $this->packDescriptor
                ],
                $sortedRow
            )
        );

        $this->journal[$this->datasetPointer] = $this->dataRow;

    }

    /**
     * @return array
     */
    public function getDataRow() : array
    {
        $this->dataRow = $this->stream->read($this->rowLength);
        return unpack(
            $this->unpackDescriptor,
            $this->dataRow
        );
    }


    /**
     * @param callable $callback
     * @param array $fields
     * @param array|null $filter
     */
    public function fetch(callable $callback, array $filter = null) : void
    {
        $this->stream->rewind();
        $this->datasetPointer = 0;
        do
        {
            $data = $this->getDataRow();
            $callback($data);
            $this->stream->seek(
                $this->datasetPointer += $this->rowLength
            );
        } while($this->stream->getSize() > $this->datasetPointer);
    }

    /**
     * lock table and flush journal to disk
     */
    public function flush($intoVoid = false) : void
    {
        $this->lock();
        if(!$intoVoid)
        {
            foreach ($this->journal as $datasetPointer => $dataRow)
            {
                $this->stream->seek($datasetPointer);
                $this->stream->write($dataRow);
            }
        }
        $this->journal = [];
        $this->unlock();
    }

    /**
     *
     */
    public function eof() : void
    {
        $this->stream->eof();
        $this->datasetPointer = $this->stream->tell();
        foreach (array_keys($this->journal) as $journalPointer)
        {
            if($this->datasetPointer < $journalPointer)
            {
                $this->datasetPointer = $journalPointer;
            }
        }

    }

    /**
     * @return string
     */
    public function getUId() : string
    {
        return $this->uId;
    }

    /**
     * @return array[Fields]
     */
    public function getFields() : array
    {
        return $this->fields;
    }

    /**
     * @param array[FieldDefinition] $fieldDefinitions
     */
    public function addDefinition(array $fieldDefinitions)
    {
        /** @var FieldDefinition $fieldDefinition */
        foreach ($fieldDefinitions as $fieldDefinition)
        {

            if(!is_a($fieldDefinition, FieldDefinition::class))
            {
                throw new \Exception('Error while setting field definitions for table "' . $this->name . '", given object was not a FieldDefinition.');
            }

            $name = $fieldDefinition->getName();
            $dataType = $fieldDefinition->getDataType();
            $byteLength = $fieldDefinition->getByteLength();

            $this->fields[$name] = new Field(
                $name,
                $dataType,
                $byteLength
            );

            switch ($fieldDefinition->getDataType())
            {
                case FieldDefinition::DATA_TYPE_CHAR:
                case FieldDefinition::DATA_TYPE_BOOL:
                    $this->packDescriptor .= 'C1';
                    $this->unpackDescriptor .= 'C1' . $name . '/';
                    break;
                case FieldDefinition::DATA_TYPE_INT:
                    $this->packDescriptor .= 'i';
                    $this->unpackDescriptor .= 'i' . $name . '/';
                    break;
                case FieldDefinition::DATA_TYPE_STRING:
                    $this->packDescriptor .= 'Z' . $byteLength;
                    $this->unpackDescriptor .= 'Z' . $byteLength . $name . '/';
            }

            $this->rowLength += (int)$fieldDefinition->getByteLength();
        }

        $this->unpackDescriptor =  rtrim($this->unpackDescriptor, '/');

    }

    /**
     * @param string $databaseFolder
     * @throws \Exception
     */
    public function connectToDisk($databaseFolder)
    {

        $path = rtrim($databaseFolder, '/\\') . '/' . $this->diskFilePath;

        if(!is_writable($path))
        {
            mkdir(
                pathinfo($path)['dirname'],
                0777,
                true
            );
            touch($path);
            touch($path . '.idx');
            touch($path . '.jnl');
            chmod($path, 0600);
            chmod($path . '.idx', 0600);
            chmod($path . '.jnl', 0600);
        }

        if(!is_writable($path))
        {
            throw new \Exception('Could not write table to disk under: "' . $path . '".');
        }

        $this->fileResource = fopen($path, 'a+');
        $this->stream = new Stream($this->fileResource);
        $this->stream->rewind();
        $this->datasetPointer = 0;

    }


    public function lock()
    {
        flock(
            $this->fileResource,
            LOCK_EX | LOCK_SH
        );
    }

    public function unlock()
    {
        flock($this->fileResource, LOCK_UN);
    }

}