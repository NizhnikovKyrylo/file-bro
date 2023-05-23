<?php

namespace App\Http\Controllers;

trait FileHelper
{
    /**
     * Get file info array
     *
     * @param string $path
     * @return array
     */
    protected function fileInfo(string $path): array
    {
        // Get file or folder info
        $file = new \SplFileInfo($path);

        return [
            'path' => substr($this->finish($file->getPathname()), strlen(config('file-browser.entry'))),
            'ctime' => $file->getCTime(),
            'mtime' => $file->getMTime(),
            'atime' => $file->getATime(),
            'type' => $file->getType(),
            'filename' => $file->getFileName(),
            'mimetype' => mime_content_type($path)
        ];
    }

    /**
     * Finish a string with an instance of a given value.
     *
     * @param string $str
     * @param string $cap
     * @return string
     */
    protected function finish(string $str, string $cap = DIRECTORY_SEPARATOR): string
    {
        return str_ends_with($str, $cap) ? $str : $str . $cap;
    }



    /**
     * Get folder size and content counts
     *
     * @param string $src
     * @param array $values
     * @return array
     */
    protected function recursiveSize(string $src, array $values = [
        'files' => 0,
        'folders' => 0,
        'size' => 0
    ]): array
    {
        // Folder content
        $list = new \DirectoryIterator($this->finish($src));

        foreach ($list as $file) {
            if (!$file->isDot() && ($file->isFile() || $file->isDir())) {
                if ($file->isDir()) {
                    // Increase folder number
                    $values['folders']++;
                    // Figure out folder size
                    $values = $this->recursiveSize($file->getPathname(), $values);
                } else {
                    // Increase file number
                    $values['files']++;
                    // Get file size
                    $values['size'] += $file->getSize();
                }
            }
        }
        return $values;
    }
}
