<?php

namespace Aatis\FileManager\Service;

use Aatis\FileManager\Exception\DirectoryNotFoundException;
use Aatis\FileManager\Exception\FailedToOpenException;
use Aatis\FileManager\Interface\FileManagerInterface;

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

    public function read(string $path): string
    {
        $content = file_get_contents($path);

        if (false === $content) {
            throw new FailedToOpenException(sprintf('Failed to open %s', $path));
        }

        return $content;
    }

    public function write(string $path, mixed $data): bool
    {
        if (false === file_put_contents($path, $data)) {
            throw new FailedToOpenException(sprintf('Failed to open %s', $path));
        }

        return true;
    }

    private function isFolder(string $path): bool
    {
        return is_dir($path);
    }
}
