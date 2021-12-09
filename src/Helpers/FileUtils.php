<?php

namespace DM\LighthouseSchemaGenerator\Helpers;

use Safe\Exceptions\FilesystemException;
use Symfony\Component\Finder\SplFileInfo;
use Illuminate\Support\Facades\File as SupportFile;

class FileUtils
{
    /**
     * Determine if a file or directory exists.
     *
     * @param  string  $path
     * @return bool
     */
    public function exists(string $path): bool
    {
        return SupportFile::exists($path);
    }

    /**
     * @param string $path
     * @return SplFileInfo[]
     */
    public function getAllFiles(string $path = ''): array
    {
        return SupportFile::allFiles(app_path($path));
    }

    /**
     * @param string $name
     * @return string
     */
    public function generateFileName(string $name): string
    {
        return strtolower("{$name}.graphql");
    }

    /**
     * @param string $path
     * @param string $content
     * @return int
     * @throws FilesystemException
     */
    public function filePutContents(string $path, string $content): int
    {
        return \Safe\file_put_contents($path, $content);
    }
}
