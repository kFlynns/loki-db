<?php

namespace KFlynns\Test;

use KFlynns\LokiDb\Db;
use KFlynns\LokiDb\Storage\Schema;
use PHPUnit\Framework\TestCase;

class Environment
{

    /** @var string  */
    private string $testTempKey;

    /**
     * @param TestCase $testCase
     */
    public function __construct(TestCase $testCase)
    {
        $this->testTempKey = join(
            '-',
            \explode('\\', \strtolower(\get_class($testCase)))
        );
        // clean last tests
        $files = \glob(__DIR__ . '/data/*');
        /** @var string $file */
        foreach($files as $file)
        {
            if (
                !\is_dir($file) ||
                \substr($file, 0, 1) === '.' ||
                \preg_match('/_[a-z0-9]{8}$/', $file) !== 1
            ) {
                continue;
            }
            $this->delTree($file);
        }
    }

    /**
     * @param string $directory
     * @return bool
     */
    protected function delTree(string $directory): bool
    {
        $files = \array_diff(\scandir($directory), ['.', '..']);
        /** @var string $file */
        foreach ($files as $file)
        {
            \is_dir($directory . '/' . $file)
                ? $this->delTree($directory . '/' . $file)
                : \unlink($directory . '/' . $file);
        }
        return \rmdir($directory);
    }

    /**
     * @return Db
     * @throws \KFlynns\LokiDb\Exception\RunTimeException
     */
    public function getTempDatabase(array $schema): Db
    {
        $directory = __DIR__
            . '/data/'
            . $this->testTempKey
            . '_'
            . \bin2hex(\random_bytes(4)) . '/';
        \mkdir($directory);
        \file_put_contents($directory . 'loki.json', \json_encode($schema));
        $schema = new Schema($directory);
        $db = new Db($schema);
        return $db;
    }

}