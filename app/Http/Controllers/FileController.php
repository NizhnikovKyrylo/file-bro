<?php

namespace App\Http\Controllers;

use App\Http\Requests\{FileManipulationRequest, FilePathExistsRequest};
use Illuminate\Http\JsonResponse;

class FileController extends AbstractFileController
{
    /**
     * File properties
     *
     * @param FilePathExistsRequest $request
     * @return JsonResponse
     */
    public function properties(FilePathExistsRequest $request): JsonResponse
    {
        // File path
        $path = $this->fullPath($request->validated('path'));
        // Check if file exists
        if (!file_exists($path)) {
            return response()->json([
                'errors' => [self::FILE_BROWSER_NOT_EXIST]
            ], 404);
        }

        $info = $this->fileInfo($path);
        // Check file is am image
        $dimensions = getimagesize($path);
        // Get image dimensions
        if ($dimensions) {
            $info['width'] = $dimensions[0];
            $info['height'] = $dimensions[1];
        }

        return response()->json($info);
    }

    /**
     * Retrieve file content
     *
     * @param FilePathExistsRequest $request
     * @return JsonResponse
     */
    public function view(FilePathExistsRequest $request): JsonResponse
    {
        // File path
        $path = $this->fullPath($request->validated('path'));
        // Check if file exists
        if (!file_exists($path)) {
            return response()->json([
                'errors' => [self::FILE_BROWSER_NOT_EXIST]
            ], 404);
        }

        return response()->json([
            'content' => file_get_contents($path)
        ]);
    }

    /**
     * Get file size in bytes
     *
     * @param FilePathExistsRequest $request
     * @return JsonResponse
     */
    public function size(FilePathExistsRequest $request): JsonResponse
    {
        // File path
        $path = $this->fullPath($request->validated('path'));
        // Check if file exists
        if (!file_exists($path)) {
            return response()->json([
                'errors' => [self::FILE_BROWSER_NOT_EXIST]
            ], 404);
        }

        return response()->json([
            'size' => filesize($path)
        ]);
    }

    /**
     * Copy file
     *
     * @param FileManipulationRequest $request
     * @return JsonResponse
     */
    public function copy(FileManipulationRequest $request): JsonResponse
    {
        $from = $this->fullPath($request->validated('from'));
        // Check if source folder exists
        if (!file_exists($from)) {
            return response()->json([
                'errors' => [self::FILE_BROWSER_NOT_EXIST]
            ], 404);
        }

        // Destination path
        $to = $this->fullPath($request->validated('to'));
        // Create destination folder if not exists
        $dest_folder = pathinfo($to, PATHINFO_DIRNAME);
        if (!file_exists($dest_folder)) {
            mkdir($dest_folder, config('file-browser.permissions.folder'), true);
        }

        try {
            copy($from, $to);
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
     * Move or rename file
     *
     * @param FileManipulationRequest $request
     * @return JsonResponse
     */
    public function move(FileManipulationRequest $request): JsonResponse
    {
        $from = $this->fullPath($request->validated('from'));
        // Check if source folder exists
        if (!file_exists($from)) {
            return response()->json([
                'errors' => [self::FILE_BROWSER_NOT_EXIST]
            ], 404);
        }

        // Folder / file destination path
        $to = $this->fullPath($request->validated('to'));
        // Create destination folder if not exists
        $dest_folder = pathinfo($to, PATHINFO_DIRNAME);
        if (!file_exists($dest_folder)) {
            mkdir($dest_folder, config('file-browser.permissions.folder'), true);
        }

        // Move file
        try {
            rename($from, $to);
        } catch (\Exception $e) {
            return response()->json([
                'errors' => [static::FILE_BROWSER_PERM_DENY, $e->getMessage()]
            ], 403);
        }

        // Return new folder path as result
        return response()->json([
            'path' => substr($to, strlen(config('file-browser.entry')))
        ]);
    }
}
