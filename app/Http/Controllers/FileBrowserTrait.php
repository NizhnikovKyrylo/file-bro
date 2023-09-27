<?php

namespace App\Http\Controllers;

use Illuminate\Support\Str;

trait FileBrowserTrait
{

    /**
     * Finish a string with an instance of a given value.
     *
     * @param string $str
     * @param string $cap
     * @return string
     */
    protected function cap(string $str, string $cap = DIRECTORY_SEPARATOR): string
    {
        return str_ends_with($str, $cap) ? $str : $str . $cap;
    }

    /**
     * Create folder
     *
     * @param string $path
     * @return string
     */
    protected function createFolder(string $path): string
    {
        if (!str_starts_with($path, config('file-browser.entry'))) {
            $path = $this->fullPath($path);
        }
        if (!file_exists($path)) {
            mkdir($path, config('file-browser.permissions.folder'), true);
        }

        return $this->prettyPath($path);
    }

    /**
     * Remove all symbols except latin characters, numbers, dashes and underscores. Remove multiple dashes
     *
     * @param string $str
     * @return string
     */
    protected function escapeName(string $str): string
    {
        return preg_replace('/-+/', '-',
            preg_replace('/[^a-zA-Z0-9_-]+/', '-', mb_strtolower(Str::ascii(trim($str))))
        );
    }

    /**
     * Folder full path
     *
     * @param string $folder
     * @return string
     */
    protected function fullPath(string $folder): string
    {
        return $this->prettyPath($this->cap(config('file-browser.entry')) . $folder);
    }

    /**
     * Get file or folder info
     *
     * @param string|\SplFileInfo $file
     * @return array
     */
    protected function instanceInfo(string|\SplFileInfo $file): array
    {
        // Get file or folder info
        if (is_string($file)) {
            $file = new \SplFileInfo($file);
        }

        $filename = $file->isFile()
            ? substr($file->getBaseName(), 0, strlen($file->getBaseName()) - strlen($file->getExtension()) - 1)
            : $file->getFileName();

        return [
            'path' => substr($this->cap($file->getPath()), strlen(config('file-browser.entry'))),
            'isDir' => $file->isDir(),
            'basename' => $file->getFileName(),
            'filename' => $filename,
            'name' => mb_strtolower($filename),
            'ext' => $file->getExtension(),
            'mime-type' => $file->isFile() ? mime_content_type($file->getPathname()) : null,
            'size' => $file->isDir() ? 4096 : $file->getSize(),
            'type' => $file->getType(),
            'ctime' => $file->getCTime(),
            'mtime' => $file->getMTime(),
            'atime' => $file->getATime()
        ];
    }

    /**
     * Remove double slashes
     *
     * @param string $path
     * @return string
     */
    protected function prettyPath(string $path): string
    {
        return preg_replace('/\/+/', '/', $path);
    }

    /**
     * Recursive copy directory with files
     *
     * @param string $from
     * @param string $to
     * @return bool
     */
    protected function recursiveCopy(string $from, string $to): bool
    {
        $list = new \DirectoryIterator($this->cap($from));

        foreach ($list as $entity) {
            if (!$entity->isDot() && ($entity->isFile() || $entity->isDir())) {
                if ($entity->isDir()) {
                    // Create a new folder name
                    $new_path = pathinfo($entity, PATHINFO_FILENAME);
                    // Create folder
                    mkdir($to . DIRECTORY_SEPARATOR . $new_path, config('file-browser.permissions.folder'), true);
                    // Copy inner folder content
                    $this->recursiveCopy(
                        $this->cap($from) . $new_path,
                        $this->cap($to) . $new_path
                    );
                } else {
                    // Copy file
                    copy($from . DIRECTORY_SEPARATOR . $entity, $to . DIRECTORY_SEPARATOR . $entity);
                }
            }
        }

        return true;
    }

    /**
     * Recursive remove folder
     *
     * @param string $path
     */
    protected function recursiveRemove(string $path): void
    {
        if (file_exists($path)) {
            if (is_file($path)) {
                // Remove file
                unlink($path);
            } else {
                $files = new \DirectoryIterator($path);

                // If directory is not empty
                foreach ($files as $file) {
                    if (!$file->isDot()) {
                        if ($file->isDir()) {
                            $this->recursiveRemove($file->getPathname());
                        } else {
                            unlink($file->getPathname());
                        }
                    }
                }

                rmdir($path);
            }
        }
    }

    /**
     * Recursive file and folder search
     *
     * @param string $find
     * @param string $path
     * @param array $results
     * @return array
     */
    protected function recursiveSearch(string $find, string $path, array $results = []): array
    {
        $files = new \DirectoryIterator($this->cap($path));

        foreach ($files as $file) {
            if (!$file->isDot() && !$file->isLink()) {
                if ($file->isDir()) {
                    if (str_contains($file->getBaseName(), $find)) {
                        $results[] = $this->instanceInfo($file);
                    }

                    if (is_readable($file->getPathname()) && count(glob($file->getPathname() . '/*')) > 0) {
                        $results = $this->recursiveSearch($find, $file->getPathname(), $results);
                    }
                } else {
                    if (str_contains($file->getBaseName(), $find)) {
                        $results[] = $this->instanceInfo($file);
                    }
                }
            }
        }

        return $results;
    }

    /**
     * Get folder size and content counts
     *
     * @param string $src
     * @param array $values
     * @return array
     */
    protected function recursiveSize(string $src, array $values = ['files' => 0, 'folders' => 0, 'size' => 0]): array
    {
        $list = new \DirectoryIterator($this->cap($src));

        foreach ($list as $item) {
            if (!$item->isDot() && !$item->isLink()) {
                if ($item->isDir()) {
                    $values['folders']++;
                    $values = $this->recursiveSize($item->getPathname(), $values);
                } else {
                    $values['files']++;
                    $values['size'] += $item->getSize();
                }
            }
        }
        return $values;
    }
}