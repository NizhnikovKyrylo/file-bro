<?php

namespace App\Http\Controllers;

use App\Http\Requests\{FileManipulationRequest, FilePathExistsRequest, FileUploadRequest};
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

class FileBrowserController extends Controller
{
    use AuthorizesRequests, FileBrowserTrait, ValidatesRequests;

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
            // Check if destination folder exists
            $to = $this->createFolder($this->cap($to) . pathinfo($from, PATHINFO_BASENAME));
            if (is_dir($from)) {
                // Copy folder
                $this->recursiveCopy($from, $to);
            } else {
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
        $result = collect();

        // Get folder file list
        $list = new \DirectoryIterator($this->cap($path));

        // Iterate through the files
        foreach ($list as $entity) {
            // Check instance is file or folder
            if (!$entity->isDot() && ($entity->isFile() || $entity->isDir())) {
                $result->push($this->instanceInfo($entity));
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

        return response()->json(is_dir($path) ? $this->recursiveSize($path) : ['size' => filesize($path)]);
    }

    /**
     * Upload file to the server
     *
     * @param FileUploadRequest $request
     * @return JsonResponse
     */
    public function upload(FileUploadRequest $request): JsonResponse
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
}
