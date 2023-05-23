<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;

class AbstractFileController extends Controller
{
    use AuthorizesRequests, FileHelper, ValidatesRequests;

    const FILE_BROWSER_NOT_EXIST = 'The file or folder on the given path does not exist.';

    const FILE_BROWSER_PERM_DENY = 'Cannot access the directory. Permission denied.';

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

    /**
     * Folder full path
     *
     * @param string $folder
     * @return string
     */
    protected function fullPath(string $folder): string
    {
        return $this->prettyPath($this->finish(config('file-browser.entry')) . $folder);
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
}
