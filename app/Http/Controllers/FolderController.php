<?php

namespace App\Http\Controllers;

use App\Http\Requests\{FileManipulationRequest, FilePathExistsRequest};
use Illuminate\Http\JsonResponse;

class FolderController extends AbstractFileController
{
    /**
     * Retrieve a list of folder containment
     *
     * @param FilePathExistsRequest $request
     * @return JsonResponse
     */
    public function list(FilePathExistsRequest $request): JsonResponse
    {
        // Folder path
        $path = $this->fullPath($request->validated('path'));
        // Check if folder exists
        if (!file_exists($path) || !is_dir($path)) {
            return response()->json([
                'errors' => [self::FILE_BROWSER_NOT_EXIST]
            ], 404);
        }

        // Folder content
        $result = [];

        // Get folder file list
        $list = new \DirectoryIterator($this->finish($path));

        // Iterate through the files
        foreach ($list as $entity) {
            // Check instance is file or folder
            if (!$entity->isDot() && ($entity->isFile() || $entity->isDir())) {
                $result[] = $this->instanceInfo($entity);
            }
        }

        return response()->json($result);
    }

    /**
     * Get folder properties
     *
     * @param FilePathExistsRequest $request
     * @return JsonResponse
     */
    public function properties(FilePathExistsRequest $request): JsonResponse
    {
        // Folder path
        $path = $this->fullPath($request->validated('path'));
        // Check if folder exists
        if (!file_exists($path) || !is_dir($path)) {
            return response()->json([
                'errors' => [self::FILE_BROWSER_NOT_EXIST]
            ], 404);
        }

        // Get folder size and folder properties
        return response()->json(array_merge(
            $this->recursiveSize($path),
            $this->fileInfo($path)
        ));
    }

    /**
     * Search file or folder in filesystem
     *
     * @param string $str
     * @param FilePathExistsRequest $request
     * @return JsonResponse
     */
    public function search(string $str, FilePathExistsRequest $request): JsonResponse
    {
        // Folder path
        $path = $this->fullPath($request->validated('path'));

        return response()->json($this->recursiveSearch($str, $path));
    }

    /**
     * Get folder size in bytes
     *
     * @param FilePathExistsRequest $request
     * @return JsonResponse
     */
    public function size(FilePathExistsRequest $request): JsonResponse
    {
        // Folder path
        $path = $this->fullPath($request->validated('path'));
        // Check if folder exists
        if (!file_exists($path) || !is_dir($path)) {
            return response()->json([
                'errors' => [self::FILE_BROWSER_NOT_EXIST]
            ], 404);
        }

        return response()->json([
            'size' => $this->recursiveSize($path)['size']
        ]);
    }

    /**
     * Create folder
     *
     * @param FilePathExistsRequest $request
     * @return JsonResponse
     */
    public function create(FilePathExistsRequest $request): JsonResponse
    {
        // Folder path
        $path = $this->fullPath($request->validated('path'));

        $info = pathinfo($path);
        $path = $info['dirname'] . DIRECTORY_SEPARATOR . $this->escapeName($info['filename']);
        // Create folder if not exists
        try {
            if (!file_exists($path)) {
                mkdir($path, config('file-browser.permissions.folder'), true);
            }
        } catch (\Exception $e) {
            return response()->json([
                'errors' => [static::FILE_BROWSER_PERM_DENY, $e->getMessage()]
            ], 403);
        }
        // Return new folder path as result
        return response()->json([
            'path' => substr($path, strlen(config('file-browser.entry')))
        ], 201);
    }

    /**
     * Copy folder with files
     *
     * @param FileManipulationRequest $request
     * @return JsonResponse
     */
    public function copy(FileManipulationRequest $request): JsonResponse
    {
        // Source folder path
        $from = $this->fullPath($request->validated('from'));
        // Check if source folder exists
        if (!file_exists($from) || !is_dir($from)) {
            return response()->json([
                'errors' => [self::FILE_BROWSER_NOT_EXIST]
            ], 404);
        }

        $to = $this->copyFolder($from, $request->validated('to'));

        return response()->json([
            'path' => substr($to, strlen(config('file-browser.entry')))
        ], 201);
    }

    /**
     * Move folder
     *
     * @param FileManipulationRequest $request
     * @return JsonResponse
     */
    public function move(FileManipulationRequest $request): JsonResponse
    {
        $from = $this->fullPath($request->validated('from'));
        // Check if source folder exists
        if (!file_exists($from) || !is_dir($from)) {
            return response()->json([
                'errors' => [self::FILE_BROWSER_NOT_EXIST]
            ], 404);
        }

        // Destination path
        $to = $this->fullPath($request->validated('to'));
        if (!file_exists($to)) {
            mkdir($to, config('file-browser.permissions.folder'), true);
        }

        // If folder is empty just rename it
        if (empty(glob($to . '/*'))) {
            rename($from, $to);
        } else {
            // Move folder
            $to = $this->copyFolder($from, $request->validated('to'));
            $this->recursiveRemove($from);
        }

        // Return new folder / file path as result
        return response()->json([
            'path' => substr($to, strlen(config('file-browser.entry')))
        ]);
    }

    /**
     * Remove folder or file
     *
     * @param FilePathExistsRequest $request
     * @return JsonResponse
     */
    public function remove(FilePathExistsRequest $request): JsonResponse
    {
        // Folder path
        $path = $this->fullPath($request->validated('path'));
        // Check if folder exists
        if (!file_exists($path) || !is_dir($path)) {
            return response()->json([
                'errors' => [self::FILE_BROWSER_NOT_EXIST]
            ], 404);
        }
        // Remove folder with contents
        try {
            $this->recursiveRemove($path);
        } catch (\Exception $e) {
            return response()->json(['errors' => [static::FILE_BROWSER_PERM_DENY, $e->getMessage()]], 403);
        }

        return response()->json([], 204);
    }


    /**
     * Get file or folder info
     *
     * @param \SplFileInfo $file
     * @return array
     */
    protected function instanceInfo(\SplFileInfo $file): array
    {
        return [
            'path' => substr($this->finish($file->getPath()), strlen(config('file-browser.entry'))),
            'isDir' => $file->isDir(),
            'basename' => $file->getFileName(),
            'filename' => $file->isFile()
                ? substr($file->getBaseName(), 0, strlen($file->getBaseName()) - strlen($file->getExtension()) - 1)
                : $file->getFileName(),
            'ext' => $file->getExtension(),
            'mime-type' => $file->isFile() ? mime_content_type($file->getPathname()) : null,
            'size' => $file->getSize(),
            'type' => $file->getType(),
            'ctime' => $file->getCTime(),
            'mtime' => $file->getMTime(),
            'atime' => $file->getATime(),
        ];
    }

    /**
     * Begin a string with an instance of a given value.
     *
     * @param string $str
     * @param string $cap
     * @return string
     */
    protected function start(string $str, string $cap = DIRECTORY_SEPARATOR): string
    {
        return str_starts_with($str, $cap) ? $str : $cap . $str;
    }

    /**
     * Run folder copy
     *
     * @param string $from
     * @param string $to
     * @return string
     */
    protected function copyFolder(string $from, string $to): string
    {
        // Destination path
        $to = $this->fullPath($to);
        if (!file_exists($to)) {
            mkdir($to, config('file-browser.permissions.folder'), true);
        }

        // Copy folder
        try {
            $this->recurseCopy($from, $to);
        } catch (\Exception $e) {
            return response()->json([
                'errors' => [static::FILE_BROWSER_PERM_DENY, $e->getMessage()]
            ], 403);
        }

        return $to;
    }

    /**
     * Recursive copy directory with files
     *
     * @param string $from
     * @param string $to
     * @return bool
     */
    protected function recurseCopy(string $from, string $to): bool
    {
        $list = new \DirectoryIterator($this->finish($from));

        foreach ($list as $entity) {
            if (!$entity->isDot() && ($entity->isFile() || $entity->isDir())) {
                if ($entity->isDir()) {
                    // Create a new folder name
                    $new_path = pathinfo($entity, PATHINFO_FILENAME);
                    // Create folder
                    mkdir($to . DIRECTORY_SEPARATOR . $new_path, config('file-browser.permissions.folder'), true);
                    // Copy inner folder content
                    $this->recurseCopy(
                        $this->finish($from) . $new_path,
                        $this->finish($to) . $new_path
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
     * Recursive file and folder search
     *
     * @param string $find
     * @param string $path
     * @param array $results
     * @return array
     */
    protected function recursiveSearch(string $find, string $path, array $results = []): array
    {
        $list = new \DirectoryIterator($this->finish($path));

        foreach ($list as $entity) {
            if (!$entity->isDot() && ($entity->isFile() || $entity->isDir())) {
                // Check entity is folder
                if ($entity->isDir()) {
                    // Folder name match
                    if (str_contains($entity->getBaseName(), $find)) {
                        $results[] = $this->instanceInfo($entity);
                    }
                    // Go through inner folders
                    if (is_readable($entity->getPathname()) && count(scandir($entity->getPathname())) > 2) {
                        $results = $this->recursiveSearch($find, $entity->getPathname(), $results);
                    }
                    // Check entity is a file
                } else if (str_contains($entity->getBaseName(), $find)) {
                    $results[] = $this->instanceInfo($entity);
                }
            }
        }

        return $results;
    }
}
