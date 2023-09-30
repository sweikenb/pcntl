<?php

namespace Tests\TestHelper;

trait TempFileTrait
{
    protected const TEST_DIR = '/tmp/phpunit';

    protected function setUp(): void
    {
        parent::setUp();
        if (!is_dir(self::TEST_DIR)) {
            mkdir(self::TEST_DIR, 0775, true);
        }
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $deleteDir = function (string $path) use (&$deleteDir): void {
            if (is_dir($path)) {
                foreach (scandir($path) as $file) {
                    if (!in_array($file, ['.', '..'])) {
                        $deleteDir(sprintf("%s/%s", self::TEST_DIR, $file));
                    }
                }
                @rmdir($path);
            } else {
                unlink($path);
            }
        };
        $deleteDir(self::TEST_DIR);
    }

    protected function getPidFile(int $pid): string
    {
        return sprintf("%s/%s.txt", self::TEST_DIR, $pid);
    }
}
