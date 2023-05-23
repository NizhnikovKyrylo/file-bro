<?php

namespace App\Http\Controllers;

use App\Http\Requests\{FileManipulationRequest, FilePathExistsRequest, FileUploadRequest};
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

class FileBrowserController extends Controller
{
    const FILE_BROWSER_RESTRICTED = 'This type of file is prohibited to upload.';

    const FILE_BROWSER_NOT_EXIST = 'The file or folder on the given path does not exist.';

    const FILE_BROWSER_PERM_DENY = 'Cannot access the directory. Permission denied.';

    /**
     * Copy file of folder
     *
     * @param FileManipulationRequest $request
     * @return JsonResponse
     */
    public function copy(FileManipulationRequest $request): JsonResponse
    {
        $from = $this->fullPath($request->validated('from'));
        // Check if source exists
        if (!file_exists($from) || (!is_dir($from) && !is_file($from))) {
            return response()->json([
                'errors' => [self::FILE_BROWSER_NOT_EXIST]
            ], 404);
        }

        $to = $this->fullPath($request->validated('to'));

        try {
            if (is_dir($from)) {
                // Check if destination folder exists
                $to = $this->createFolder($this->cap($to) . pathinfo($from, PATHINFO_BASENAME));
                // Copy folder
                $this->recursiveCopy($from, $to);
            } else {
                // Check if destination folder exists
                $to = $this->createFolder($this->cap($to)) . pathinfo($from, PATHINFO_BASENAME);
                // Copy file
                copy($from, $to);
            }
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
     * Create folder
     *
     * @param FilePathExistsRequest $request
     * @return JsonResponse
     */
    public function create(FilePathExistsRequest $request): JsonResponse
    {
        // Folder path
        $path = $this->fullPath($request->validated('path'));
        // Remove special chars from folder path
        $info = pathinfo($path);
        $path = $info['dirname'] . DIRECTORY_SEPARATOR . $this->escapeName($info['filename']);
        // Create folder if not exists
        try {
            $path = $this->createFolder($path);
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
     * Get folder content
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
        $list = new \DirectoryIterator($this->cap($path));

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
     * Move/Rename folder or file
     *
     * @param FileManipulationRequest $request
     * @return JsonResponse
     */
    public function move(FileManipulationRequest $request): JsonResponse
    {
        $from = $this->fullPath($request->validated('from'));
        // Check if source exists
        if (!file_exists($from) || (!is_dir($from) && !is_file($from))) {
            return response()->json([
                'errors' => [self::FILE_BROWSER_NOT_EXIST]
            ], 404);
        }

        $to = $this->fullPath($request->validated('to'));

        if (is_file($from) || empty(glob($to . '/*'))) {
            if (file_exists($to)) {
                if (is_dir($to)) {
                    $to = $this->cap($to) . pathinfo($from, PATHINFO_BASENAME);
                } else {
                    unlink($to);
                }
            }
            rename($from, $to);
        } else {
            $to = $this->createFolder($this->cap($to) . pathinfo($from, PATHINFO_BASENAME));
            // Copy folder
            $this->recursiveCopy($from, $to);
            // Remove "from" folder
            $this->recursiveRemove($from);
        }

        // Return new folder / file path as result
        return response()->json([
            'path' => substr($to, strlen(config('file-browser.entry')))
        ]);
    }

    /**
     * Get folder or file info
     *
     * @param FilePathExistsRequest $request
     * @return JsonResponse
     */
    public function info(FilePathExistsRequest $request): JsonResponse
    {
        $path = $this->fullPath($request->validated('path'));
        // Check if folder or file exists
        if (!file_exists($path) || (!is_dir($path) && !is_file($path))) {
            return response()->json([
                'errors' => [self::FILE_BROWSER_NOT_EXIST]
            ], 404);
        }

        $result = $this->instanceInfo($path);

        if (is_file($path)) {
            $info = @getimagesize($path);
            if ($info) {
                $result['width'] = $info[0];
                $result['height'] = $info[1];
            }
        }

        return response()->json($result);
    }

    /**
     * Remove file or folder
     *
     * @param FilePathExistsRequest $request
     * @return JsonResponse
     */
    public function remove(FilePathExistsRequest $request): JsonResponse
    {
        $path = $this->fullPath($request->validated('path'));
        // Check if folder or file exists
        if (!file_exists($path)) {
            return response()->json([], 204);
        }
        // Remove folder or file
        try {
            $this->recursiveRemove($path);
        } catch (\Exception $e) {
            return response()->json([
                'errors' => [static::FILE_BROWSER_PERM_DENY, $e->getMessage()]
            ], 403);
        }

        return response()->json([], 204);
    }

    /**
     * Search file or folder
     *
     * @param string $name
     * @param FilePathExistsRequest $request
     * @return JsonResponse
     */
    public function search(string $name, FilePathExistsRequest $request): JsonResponse
    {
        $path = $this->fullPath($request->validated('path'));
        // Check if folder or file exists
        if (!file_exists($path) || (!is_dir($path) && !is_file($path))) {
            return response()->json([
                'errors' => [self::FILE_BROWSER_NOT_EXIST]
            ], 404);
        }

        return response()->json($this->recursiveSearch($this->escapeName($name), $path));
    }

    /**
     * Figure out folder size or get file size
     *
     * @param FilePathExistsRequest $request
     * @return JsonResponse
     */
    public function size(FilePathExistsRequest $request): JsonResponse
    {
        $path = $this->fullPath($request->validated('path'));
        // Check if folder or file exists
        if (!file_exists($path) || (!is_dir($path) && !is_file($path))) {
            return response()->json([
                'errors' => [self::FILE_BROWSER_NOT_EXIST]
            ], 404);
        }

        return response()->json([
            'size' => is_dir($path) ? $this->recursiveSize($path) : filesize($path)
        ]);
    }

    public function upload(FileUploadRequest $request)
    {
        $args = $request->validated();
        // Get restricted file extensions and mimetypes
        $restricted = array_flip(config('file-browser.restricted'));
        // Get file extension
        $ext = $args['file']->guessExtension();
        // Check if file extension or mimetype is restricted
        if (isset($restricted[$args['file']->getMimeType()]) || isset($restricted[$ext])) {
            return response()->json([
                'errors' => [self::FILE_BROWSER_RESTRICTED]
            ], 403);
        }
        // Original file name
        $filename = $args['file']->getClientOriginalName();
        // Check if new file name exists or set original name
        $name = $this->escapeName($args['name'] ?? substr($filename, 0, strrpos($filename, '.'))) . '.' . $ext;
        try {
            // Move file to the destination folder
            return response()->json(
                $this->instanceInfo(
                    $args['file']->move(
                        $this->createFolder($this->fullPath($args['path'])),
                        $name
                    )
                ),
                201
            );
        } catch (\Exception $e) {
            return response()->json([
                'errors' => [static::FILE_BROWSER_PERM_DENY, $e->getMessage()]
            ], 403);
        }
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

        return [
            'path' => substr($this->cap($file->getPath()), strlen(config('file-browser.entry'))),
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
    protected function recursiveSize(string $src, array $values = []): array
    {
        $list = new \DirectoryIterator($this->cap($src));

        if (empty($values)) {
            $values = [
                'files' => 0,
                'folders' => 0,
                'size' => 0,
            ];
        }
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
