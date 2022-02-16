<?php

namespace LokiDb;

/**
 * Class Btree
 * @package LokiDb
 */
class Btree
{

    /** @var array  */
    protected $dataTree;

    /** @var int  */
    protected $size;

    /** @var int  */
    protected $pointer;

    /** @var array  */
    protected $rightPointers;

    /** @var array  */
    protected  $leftPointers;

    /** @var string */
    protected $searchTerm = '';

    /** @var int */
    protected $searchIndex = 0;


    /**
     * Btree constructor.
     * @param array $data
     */
    public function __construct($data)
    {
        $this->pointer = 0;
        $this->rightPointers = [];
        $this->leftPointers = [];

        $this->dataTree = $data;
        $this->size = count($data);
        $this->constructBinaryTree();

        echo 'left' . "\n";
        print_r($this->leftPointers);
        echo 'right' . "\n";
        print_r($this->rightPointers);


    }

    /**
     * @param $direction
     * @return false|string
     * @throws \Exception
     */
    public function pack($direction)
    {
        if($direction !== 'left' && $direction !== 'right')
        {
            throw new \Exception(self::class . ': use "left" or "right" as argument.');
        }
        return pack("V*", ...$this->{$direction . 'Pointers'});
    }


    public function unpack($direction, $binaryData)
    {

    }






    /**
     * define left- and rightPointers
     */
    protected function constructBinaryTree()
    {
        for ($i = 0; $i < $this->size; $i++)
        {
            $this->pointer = $i;
            $this->followPointer(0);
        }
    }

    /**
     * @param int $root
     */
    private function followPointer($root)
    {
        if ($this->dataTree[$this->pointer] > $this->dataTree[$root])
        {
            $pointer = &$this->rightPointers;
        }
        else if ($this->dataTree[$this->pointer] < $this->dataTree[$root])
        {
            $pointer = &$this->leftPointers;
        }
        if ($pointer[$root] ?? 0 !== 0)
        {
            $this->followPointer($pointer[$root]);
            return;
        }
        $pointer[$root] = $this->pointer;
    }


    /**
     * @param mixed $value
     * @return mixed
     */
    public function search($value)
    {
        $this->searchTerm = $value;
        $this->treeSearch(0);
        return $this->searchIndex - 1;
    }

    /**
     * @param int $root
     */
    protected function treeSearch($root)
    {

        print_r($root);

        if (
            $this->searchTerm > $this->dataTree[$root] &&
            $this->rightPointers[$root] !== 0
        ) {
            echo ' right' . "\n";
            $this->treeSearch($this->rightPointers[$root]);
            return;
        }
        else if (
            $this->searchTerm < $this->dataTree[$root] &&
            $this->leftPointers[$root] !== 0
        ) {
            echo ' left' . "\n";
            $this->treeSearch($this->leftPointers[$root]);
            return;
        }
        echo ' found' . "\n";
        $this->searchIndex = $root;
    }

}