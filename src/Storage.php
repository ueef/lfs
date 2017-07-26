<?php

namespace Ueef\Lfs {

    use Throwable;
    use Ueef\Lfs\Interfaces\StorageInterface;
    use Ueef\Lfs\Exceptions\WrongKeyException;
    use Ueef\Lfs\Exceptions\CantLinkException;
    use Ueef\Lfs\Exceptions\NotExistsException;
    use Ueef\Lfs\Exceptions\CantMakeDirectoryException;

    class Storage implements StorageInterface
    {
        const MODE = 0755;
        const KEY_LENGTH = 6;
        const KEY_ENCODED_LENGTH = 8;
        const TRIES_LIMIT = 256;

        /**
         * @var string
         */
        private $dir;


        public function __construct(string $dir)
        {
            $this->dir = $this->correctPath($dir);
        }

        public function store(string $tmpPath): string
        {
            if (!file_exists($tmpPath)) {
                throw new NotExistsException(['Path "%s" doesn\'t exist', $tmpPath]);
            }

            while (true) {
                static $triesCount = 0;

                $key = $this->buildKey();
                $path = $this->getPath($key, true);

                if (link($tmpPath, $path)) {
                    return $key;
                }

                $triesCount++;

                if ($triesCount > self::TRIES_LIMIT) {
                    throw new CantLinkException(['Can\'t link "%s" to "%s"', $tmpPath, $path]);
                }
            }
        }

        public function getUrl(string $key): string
        {
            if (strlen($key) != self::KEY_ENCODED_LENGTH) {
                throw new WrongKeyException(['Wrong key "%s" required length is "%s"', $key, self::KEY_ENCODED_LENGTH]);
            }

            return preg_replace('/(.{2})(.{2})(.+)/', '/$1/$2/$3', $key);
        }

        public function getPath(string $key, bool $makeDir = false): string
        {
            $path = $this->dir . $this->getUrl($key);

            if ($makeDir) {
                $dir = dirname($path);

                if (!is_dir($dir)) {
                    try {
                        $success = mkdir($dir, self::MODE, true);
                    } catch (Throwable $e) {
                        $success = false;
                    }

                    if (!$success) {
                        throw new CantMakeDirectoryException(['Can\'t make directory "%s"', $dir]);
                    }
                }
            }

            return $path;
        }

        private function buildKey()
        {
            $key = random_bytes(self::KEY_LENGTH);
            $key = base64_encode($key);
            $key = strtr($key, '+/', '-_');
            $key = rtrim($key, '=');

            return $key;
        }

        private function correctPath(string $path)
        {
            return '/' . trim($path, '/');
        }
    }
}