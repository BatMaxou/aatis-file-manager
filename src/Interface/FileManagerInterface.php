<?php

namespace Aatis\FileManager\Interface;

use Aatis\FileManager\Exception\DirectoryNotFoundException;

interface FileManagerInterface
{
    public function exists(string $path): bool;

    public function createDirectory(string $path, int $mode = 0755, bool $recursive = false): bool;

    public function createFile(string $path, int $mode = 0644, bool $recursive = false): bool;

    public function deleteDirectory(string $directory, bool $recursive = false): bool;

    public function deleteFile(string $path): bool;

    /**
     * @return array<string>
     *
     * @throws DirectoryNotFoundException
     */
    public function getFolder(string $directory): array;

    public function copy(string $source, string $destination): bool;

    public function move(string $source, string $destination): bool;

    public function read(string $path): string;

    public function write(string $path, mixed $data, int $flag = 0): bool;

    public function append(string $path, mixed $data): bool;
}
