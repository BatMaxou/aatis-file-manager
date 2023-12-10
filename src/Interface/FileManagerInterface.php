<?php

namespace Aatis\FileManager\Interface;

use Aatis\FileManager\Exception\DirectoryNotFoundException;

interface FileManagerInterface
{
    public function createDirectory(string $path, int $permissions = 0o777): bool;

    public function create(string $path): bool;

    public function deleteDirectory(string $directory, bool $recursive = false): bool;

    public function delete(string $path): bool;

    /**
     * @return array<string>
     *
     * @throws DirectoryNotFoundException
     */
    public function getContent(string $directory): array;

    /**
     * @return array<string>
     */
    public function getFolders(string $directory): array;

    /**
     * @return array<string>
     */
    public function getFiles(string $directory, int $sort): array;

    public function copy(string $source, string $destination): bool;

    public function move(string $source, string $destination): bool;

    public function open(string $path, string $mode): bool;

    public function read(string $path, bool $open): ?string;

    public function write(string $path, mixed $data, bool $open): bool;
}
