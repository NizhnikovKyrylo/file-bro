<?php

namespace App\Http\Controllers;

use App\Http\Requests\{FolderDestinationRequest, FolderPathRequest};
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

class FolderController extends Controller
{
    const FILE_BROWSER_NOT_EXIST = 'The file or folder on the given path does not exist.';

    const FILE_BROWSER_PERM_DENY = 'Cannot access the directory. Permission denied.';

    /**
     * Retrieve a list of folder containment
     *
     * @param FolderPathRequest $request
     * @return JsonResponse
     */
    public function list(FolderPathRequest $request): JsonResponse
    {
        // Folder path
        $path = $this->prettyPath(config('file-browser.entry') . DIRECTORY_SEPARATOR . $request->validated('path'));
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
     * @param FolderPathRequest $request
     * @return JsonResponse
     */
    public function properties(FolderPathRequest $request): JsonResponse
    {
        // Folder path
        $path = $this->prettyPath(config('file-browser.entry') . DIRECTORY_SEPARATOR . $request->validated('path'));
        // Check if folder exists
        if (!file_exists($path) || !is_dir($path)) {
            return response()->json([
                'errors' => [self::FILE_BROWSER_NOT_EXIST]
            ], 404);
        }
        // Get file or folder info
        $entity = new \SplFileInfo($path);
        // Get folder size and folder properties
        return response()->json(array_merge(
            $this->recursiveSize($path),
            [
                'path' => substr($this->finish($entity->getPathname()), strlen(config('file-browser.entry'))),
                'ctime' => $entity->getCTime(),
                'mtime' => $entity->getMTime(),
                'atime' => $entity->getATime(),
                'type' => $entity->getType(),
                'filename' => $entity->getFileName()
            ]
        ));
    }

    /**
     * Search file or folder in filesystem
     *
     * @param string $str
     * @param FolderPathRequest $request
     * @return JsonResponse
     */
    public function search(string $str, FolderPathRequest $request): JsonResponse
    {
        // Folder path
        $path = $this->prettyPath(config('file-browser.entry') . DIRECTORY_SEPARATOR . $request->validated('path'));
        // Check if folder exists
        if (!file_exists($path) || !is_dir($path)) {
            return response()->json([
                'errors' => [self::FILE_BROWSER_NOT_EXIST]
            ], 404);
        }

        return response()->json($this->recursiveSearch($str, $path));
    }

    /**
     * Get folder size in bytes
     *
     * @param FolderPathRequest $request
     * @return JsonResponse
     */
    public function size(FolderPathRequest $request): JsonResponse
    {
        // Folder path
        $path = $this->prettyPath(config('file-browser.entry') . DIRECTORY_SEPARATOR . $request->validated('path'));
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
     * @param FolderPathRequest $request
     * @return JsonResponse
     */
    public function create(FolderPathRequest $request): JsonResponse
    {
        // Folder path
        $path = $this->prettyPath(config('file-browser.entry') . DIRECTORY_SEPARATOR . $request->validated('path'));
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
     * @param FolderDestinationRequest $request
     * @return JsonResponse
     */
    public function copy(FolderDestinationRequest $request): JsonResponse
    {
        // Source folder path
        $from = $this->prettyPath(config('file-browser.entry') . DIRECTORY_SEPARATOR . $request->validated('from'));
        // Check if source folder exists
        if (!file_exists($from) || !is_dir($from)) {
            return response()->json([
                'errors' => [self::FILE_BROWSER_NOT_EXIST]
            ], 404);
        }
        // Destination path
        $to = $this->prettyPath(config('file-browser.entry') . DIRECTORY_SEPARATOR . $request->validated('to'));
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

        return response()->json([
            'path' => substr($to, strlen(config('file-browser.entry')))
        ], 201);
    }

    /**
     * Rename folder or file
     *
     * @param FolderDestinationRequest $request
     * @return JsonResponse
     */
    public function rename(FolderDestinationRequest $request): JsonResponse
    {
        $from = $this->prettyPath(config('file-browser.entry') . DIRECTORY_SEPARATOR . $request->validated('from'));
        // Check if source folder exists
        if (!file_exists($from) || !is_dir($from)) {
            return response()->json([
                'errors' => [self::FILE_BROWSER_NOT_EXIST]
            ], 404);
        }
        // Check if last symbol is '/'
        if (strrpos($from, DIRECTORY_SEPARATOR) !== false) {
            $from = substr($from, 0, -1);
        }
        // New file or folder name
        $to = $this->prettyPath(
            $this->finish(substr($from, 0, strrpos($from, '/'))) . // Get $from parent folder
            $this->escapeName($request->validated('to')) // Escape destination filename
        );
        // Run renaming
        try {
            rename($from, $to);
        } catch (\Exception $e) {
            return response()->json([
                'errors' => [static::FILE_BROWSER_PERM_DENY, $e->getMessage()]
            ], 403);
        }
        // Return new folder / file path as result
        return response()->json([
            'path' => substr($to, strlen(config('file-browser.entry')))
        ]);
    }

    /**
     * Move folder
     *
     * @param FolderDestinationRequest $request
     * @return JsonResponse
     */
    public function move(FolderDestinationRequest $request): JsonResponse
    {
        $from = $this->prettyPath(config('file-browser.entry') . DIRECTORY_SEPARATOR . $request->validated('from'));
        // Check if source folder exists
        if (!file_exists($from) || !is_dir($from)) {
            return response()->json([
                'errors' => [self::FILE_BROWSER_NOT_EXIST]
            ], 404);
        }
        $folder_name = pathinfo($from, PATHINFO_FILENAME);
        // Destination path
        $to = $this->prettyPath(config('file-browser.entry') . DIRECTORY_SEPARATOR . $request->validated('to') . DIRECTORY_SEPARATOR . $folder_name);
        if (!file_exists($to)) {
            mkdir($to, config('file-browser.permissions.folder'), true);
        }
        // Move folder
        rename($from, $to);

        // Return new folder / file path as result
        return response()->json([
            'path' => substr($to, strlen(config('file-browser.entry')))
        ]);
    }

    /**
     * Remove folder
     *
     * @param FolderPathRequest $request
     * @return JsonResponse
     */
    public function remove(FolderPathRequest $request): JsonResponse
    {
        // Folder path
        $path = $this->prettyPath(config('file-browser.entry') . DIRECTORY_SEPARATOR . $request->validated('path'));
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
     * Remove double slashes
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
     * Recursive remove folder
     *
     * @param string $path
     */
    protected function recursiveRemove(string $path): void
    {
        if (file_exists($path)) {
            if (is_file($path)) {
                unlink($path);
            } else {
                $files = new \DirectoryIterator($path);

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
     * Get folder size and content counts
     *
     * @param string $src
     * @param array $values
     * @return array
     */
    protected function recursiveSize(
        string $src,
        array  $values = [
            'files' => 0,
            'folders' => 0,
            'size' => 0
        ]
    ): array
    {
        // Folder content
        $list = new \DirectoryIterator($this->finish($src));

        foreach ($list as $entity) {
            if (!$entity->isDot() && ($entity->isFile() || $entity->isDir())) {
                if ($entity->isDir()) {
                    // Increase folder number
                    $values['folders']++;
                    // Figure out folder size
                    $values = $this->recursiveSize($entity->getPathname(), $values);
                } else {
                    // Increase file number
                    $values['files']++;
                    // Get file size
                    $values['size'] += $entity->getSize();
                }
            }
        }
        return $values;
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

    /**
     * Remove all symbols except latin characters, numbers, dashes and underscores. Remove multiple dashes
     *
     * @param string $str
     * @return string
     */
    protected function escapeName(string $str): string
    {
        return preg_replace('/-+/', '-',
            preg_replace('/[^a-zA-Z0-9_-]+/', '-', mb_strtolower(trim(Str::ascii($str))))
        );
    }
}
