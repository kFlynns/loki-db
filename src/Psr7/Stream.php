<?php

namespace KFlynns\LokiDb\Psr7;

use Psr\Http\Message\StreamInterface;

class Stream implements StreamInterface
{

    /** @var resource  */
    protected $resource = null;

    /** @var array  */
    protected array $metaData;

    /** @var int  */
    protected int $size = -1;

    /**
     * @param $resource
     */
    public function __construct($resource)
    {
        if (!\is_resource($resource))
        {
            throw new \RuntimeException(self::class . ' constructor argument must be a resource.');
        }
        $this->resource = $resource;
        $this->metaData = \stream_get_meta_data($this->resource);
        $this->metaData['isReadable'] = \in_array(
            $this->metaData['mode'],
            ['r', 'a+', 'ab+', 'w+', 'wb+', 'x+', 'xb+', 'c+', 'cb+']
        );
        $this->metaData['isWriteable'] = \in_array(
            $this->metaData['mode'],
            ['/a', 'w', 'r+', 'rb+', 'rw', 'x', 'c']
        );
    }

    /**
     *
     */
    public function __destruct()
    {
        $this->close();
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        if ($this->isSeekable())
        {
            $this->seek(0);
        }
        return $this->getContents();
    }

    /**
     * @return void
     */
    public function close(): void
    {
        if ($this->resource !== null)
        {
            \fclose($this->resource);
        }
        $this->detach();
    }

    /**
     * @return resource|null
     */
    public function detach()
    {
        $resource = $this->resource;
        $this->resource = null;
        $this->metaData = [];
        return $resource;
    }


    /**
     * @return int
     */
    public function getSize(): int
    {
        if ($this->size !== -1)
        {
            return $this->size;
        }
        $stats = \fstat($this->resource);
        if (\is_array($stats))
        {
            $this->size = (int)($stats['size'] ?? -1);
            return $this->size;
        }
        throw new \RuntimeException(self::class . ': the size of the stream could not be determined.');
    }


    /**
     * @return false|int
     */
    public function tell(): int
    {
        if ($this->resource === null)
        {
            throw new \RuntimeException(self::class . ': the stream was detached.');
        }
        $result = \ftell($this->resource);
        if ($result === false)
        {
            throw new \RuntimeException(self::class . ': the position in the stream could no be determined.');
        }
        return $result;
    }

    /**
     * @return bool
     */
    public function eof(): bool
    {
        if ($this->resource === null)
        {
            throw new \RuntimeException(self::class . 'the stream is detached.');
        }
        return \feof($this->resource);
    }

    /**
     * @return bool
     */
    public function isSeekable(): bool
    {
        return $this->metaData['seekable'] ?? false;
    }

    /**
     * @param $offset
     * @param $whence
     * @return int
     */
    public function seek($offset, $whence = SEEK_SET): int
    {
        if ($this->resource === null || !$this->isSeekable())
        {
            throw new \RuntimeException(self::class . ': the stream was detached or is not seekable.');
        }
        $result = \fseek($this->resource, $whence);
        if ($result === false)
        {
            throw new \RuntimeException(self::class . ': the position in the stream could no be changed.');
        }
        return $result;
    }

    /**
     * @return int
     */
    public function rewind(): int
    {
        return $this->seek(0);
    }

    /**
     * @return bool
     */
    public function isWritable(): bool
    {
        return $this->getMetadata('isWriteable');
    }

    public function write($string)
    {
        if ($this->resource === null || !$this->isWritable())
        {
            throw new \RuntimeException(self::class . ': the stream was detached or is not writeable.');
        }
        if( false === \fwrite($this->resource, $string))
        {
            throw new \RuntimeException(self::class . ': failed to write in the stream.');
        }
        $this->size = -1;
    }

    /**
     * @return bool
     */
    public function isReadable(): bool
    {
        return $this->getMetadata('isReadable');
    }

    /**
     * @param $length
     * @return string
     */
    public function read($length): string
    {
        if ($this->resource === null || !$this->isReadable())
        {
            throw new \RuntimeException(self::class . ': the stream was detached or is not readable.');
        }
        $length = $length < 0 ? 0 : $length;
        $content = \fread($this->resource, $length);
        if (false === $content)
        {
            throw new \RuntimeException(self::class . ': could not read from stream.');
        }
        return $content;
    }

    /**
     * @return string
     */
    public function getContents(): string
    {
        if ($this->resource === null)
        {
            throw new \RuntimeException(self::class . ': the stream was detached.');
        }
        $contents = \stream_get_contents($this->resource);
        if ($contents === false)
        {
            throw new \RuntimeException(self::class . ': the stream could ne be read.');
        }
        return $contents;
    }

    /**
     * @param $key
     * @return mixed
     */
    public function getMetadata($key = null): mixed
    {
        return $this->metaData[$key] ?? null;
    }

}