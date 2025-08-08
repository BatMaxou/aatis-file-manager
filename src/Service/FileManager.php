<?php

namespace Aatis\FileManager\Service;

use Aatis\FileManager\Exception\DirectoryNotFoundException;
use Aatis\FileManager\Exception\FailedToOpenException;
use Aatis\FileManager\Exception\FileNotFoundException;
use Aatis\FileManager\Exception\SourceNotFoundException;
use Aatis\FileManager\Interface\FileManagerInterface;

class FileManager implements FileManagerInterface
{
    public function exists(string $path): bool
    {
        return file_exists($path) || $this->isFolder($path);
    }

    public function createDirectory(string $path, int $mode = 0755, bool $recursive = false): bool
    {
        if ($this->exists($path) && $this->isFolder($path)) {
            return true;
        }

        return mkdir($path, $mode, $recursive);
    }

    public function createFile(string $path, int $mode = 0644, bool $recursive = false): bool
    {
        if ($this->exists($path)) {
            return true;
        }

        if ($recursive) {
            $directory = dirname($path);
            if (!$this->createDirectory($directory, 0755, true)) {
                return false;
            }
        }

        return touch($path) && chmod($path, $mode);
    }

    public function deleteDirectory(string $directory, bool $recursive = false): bool
    {
        if (!$this->exists($directory)) {
            return true;
        }

        if (!$recursive) {
            return rmdir($directory);
        }

        $success = true;
        foreach ($this->getFolder($directory) as $item) {
            if (!$success) {
                continue;
            }

            $path = \sprintf('%s/%s', rtrim($directory, '/'), $item);
            if ($this->isFolder($path)) {
                $success = $this->deleteDirectory($path, true);
            } else {
                $success = $this->deleteFile($path);
            }
        }

        return rmdir($directory) && $success;
    }

    public function deleteFile(string $path): bool
    {
        if (!$this->exists($path)) {
            return true;
        }

        return unlink($path);
    }

    public function getFolder(string $directory): array
    {
        if (!$this->exists($directory)) {
            throw new DirectoryNotFoundException(sprintf('Path %s does not exist.', $directory));
        }

        if (!$this->isFolder($directory)) {
            throw new DirectoryNotFoundException(sprintf('Path %s is not a directory.', $directory));
        }

        $content = scandir($directory);

        if (!$content) {
            return [];
        }

        return array_diff($content, ['.', '..']);
    }

    public function copy(string $source, string $destination): bool
    {
        if (!$this->exists($source)) {
            throw new SourceNotFoundException(\sprintf('Source path %s does not exist.', $source));
        }

        return copy($source, $destination);
    }

    public function move(string $source, string $destination): bool
    {
        if (!$this->exists($source)) {
            throw new SourceNotFoundException(\sprintf('Source path %s does not exist.', $source));
        }

        if ($this->copy($source, $destination)) {
            return false;
        }

        return $this->deleteFile($source);
    }

    public function read(string $path): string
    {
        if (!$this->exists($path)) {
            throw new FileNotFoundException(\sprintf('File %s does not exist.', $path));
        }

        $content = file_get_contents($path);

        if (false === $content) {
            throw new FailedToOpenException(\sprintf('Failed to open %s', $path));
        }

        return $content;
    }

    public function write(string $path, mixed $data, int $flag = 0): bool
    {
        if (!$this->exists($path)) {
            throw new FileNotFoundException(\sprintf('File %s does not exist.', $path));
        }

        if (false === file_put_contents($path, $data, $flag)) {
            throw new FailedToOpenException(\sprintf('Failed to open %s', $path));
        }

        return true;
    }

    public function append(string $path, mixed $data): bool
    {
        return $this->write($path, $data, FILE_APPEND);
    }

    private function isFolder(string $path): bool
    {
        return is_dir($path);
    }
}
