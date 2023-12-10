<?php

namespace Aatis\FileManager\Service;

use Aatis\FileManager\Interface\FileManagerInterface;
use Aatis\FileManager\Exception\DirectoryNotFoundException;
use Aatis\FileManager\Exception\FailedToOpenException;

class FileManager implements FileManagerInterface
{
    public function createDirectory(string $path, int $permissions = 0o777): bool
    {
        return mkdir($path, $permissions, true);
    }

    public function create(string $path): bool
    {
        return touch($path);
    }

    public function deleteDirectory(string $directory, bool $recursive = false): bool
    {
        if (!$recursive) {
            return rmdir($directory);
        }

        $success = true;

        foreach ($this->getContent($directory) as $item) {
            if (!$success) {
                continue;
            } elseif ($this->isFolder($item)) {
                $success = $this->deleteDirectory($item, true);
            } else {
                $success = $this->delete($item);
            }
        }

        return $success;
    }

    public function delete(string $path): bool
    {
        return unlink($path);
    }

    public function getContent(string $directory): array
    {
        if (!$this->isFolder($directory)) {
            throw new DirectoryNotFoundException(sprintf('Path %s is not a directory.', $directory));
        }

        $content = scandir($directory);

        if (!$content) {
            return [];
        }

        return array_diff($content, ['.', '..']);
    }

    public function getFolders(string $directory): array
    {
        return array_filter($this->getContent($directory), fn ($item) => $this->isFolder($item));
    }

    public function getFiles(string $directory, int $sort = SCANDIR_SORT_ASCENDING): array
    {
        return array_filter($this->getContent($directory), fn ($item) => !$this->isFolder($item));
    }

    public function copy(string $source, string $destination): bool
    {
        return copy($source, $destination);
    }

    public function move(string $source, string $destination): bool
    {
        if ($this->copy($source, $destination)) {
            return false;
        }

        return $this->delete($source);
    }

    public function open(string $path, string $mode = 'r'): bool
    {
        return fopen($path, $mode) ? true : false;
    }

    public function read(string $path, bool $open = false): ?string
    {
        if ($open && !$this->open($path)) {
            throw new FailedToOpenException(sprintf('Failed to open %s', $path));
        }

        $content = file_get_contents($path);

        return $content ? $content : '';
    }

    public function write(string $path, mixed $data, bool $open = false): bool
    {
        if ($open && !$this->open($path)) {
            throw new FailedToOpenException(sprintf('Failed to open %s', $path));
        }

        return file_put_contents($path, $data) ? true : false;
    }

    private function isFolder(string $path): bool
    {
        return is_dir($path);
    }
}
