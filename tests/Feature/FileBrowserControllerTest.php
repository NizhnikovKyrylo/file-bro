<?php

namespace Tests\Feature;

use Faker\Factory as Faker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class FileBrowserControllerTest extends TestCase
{
    protected string $root;

    protected array $info_fields = [
        'path',
        'isDir',
        'basename',
        'filename',
        'ext',
        'mime-type',
        'size',
        'type',
        'ctime',
        'mtime',
        'atime'
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->faker = Faker::create();

        $this->root = config('file-browser.entry');

        // Seed folders
        for ($i = 0, $n = mt_rand(2, 4); $i < $n; $i++) {
            $path = $this->root . DIRECTORY_SEPARATOR . implode('/', $this->faker->words(mt_rand(1, 3)));
            if (!file_exists($path)) {
                mkdir($path, 0755, true);
            }
        }

        // Seed files
        for ($i = 0, $n = mt_rand(4, 16); $i < $n; $i++) {
            $folder = Arr::random(glob($this->root . '/*', GLOB_ONLYDIR));

            if (mt_rand(0, 1)) {
                $inner = glob($folder . '/*', GLOB_ONLYDIR);
                if (!empty($inner)) {
                    $folder = Arr::random($inner);
                }
            }

            file_put_contents($folder . '/' . implode('-', $this->faker->words(mt_rand(1, 3))) . '.txt', '1');
        }
    }

    protected function tearDown(): void
    {
        parent::tearDown();

//        $this->recursiveRemove($this->root);
    }

    /**
     * Copy file test
     *
     * @return void
     */
    public function testCopyFile()
    {
        $data = [
            'from' => '/' . uniqid() . '.txt',
            'to' => substr(Arr::random(glob($this->root . '/*', GLOB_ONLYDIR)), strlen($this->root))
        ];
        file_put_contents($this->root . '/' . $data['from'], '1');

        $this->withoutMiddleware()
            ->post(route('file-browser.copy'), $data)
            ->assertCreated()
            ->assertExactJson([
                'path' => $data['to'] . $data['from']
            ]);

        $this->assertFileExists($this->root . $data['from']);
        $this->assertFileExists($this->root . $data['to'] . $data['from']);
    }

    /**
     * Copy folder test
     *
     * @return void
     */
    public function testCopyFolder(): void
    {
        $data = array_combine(['from', 'to'], array_map(
            fn($file) => substr($file, strlen($this->root)),
            Arr::random(glob($this->root . '/*'), 2)
        ));

        $goal_folder = $data['to'] . '/' . pathinfo($data['from'], PATHINFO_BASENAME);

        $this->withoutMiddleware()
            ->post(route('file-browser.copy'), $data)
            ->assertCreated()
            ->assertExactJson([
                'path' => $goal_folder
            ]);

        $this->assertFileExists($this->root . $data['to']);
        $this->assertFileExists($this->root . $goal_folder);
    }

    /**
     * Create folder test
     *
     * @return void
     */
    public function testCreateFolder(): void
    {
        $folder = implode('/', $this->faker->words(mt_rand(1, 3)));

        $this->withoutMiddleware()
            ->post(route('file-browser.create'), ['path' => $folder])
            ->assertCreated()
            ->assertExactJson([
                'path' => '/' . $folder
            ]);

        $this->assertFileExists($this->root . '/' . $folder);
    }

    /**
     * Test file info
     *
     * @return void
     */
    public function testFileInfo(): void
    {
        $file = '/' . uniqid() . '.txt';
        file_put_contents($this->root . $file, '1');
        $info = pathinfo($file);

        $this->withoutMiddleware()
            ->post(route('file-browser.info'), ['path' => $file])
            ->assertOk()
            ->assertJsonStructure($this->info_fields)
            ->assertJsonFragment([
                'path' => $info['dirname'],
                'isDir' => false,
                'basename' => $info['basename'],
                'filename' => $info['filename'],
                'ext' => 'txt',
                'mime-type' => 'application/octet-stream',
                'size' => 1,
                'type' => 'file'
            ]);
    }

    /**
     * Test folder info
     *
     * @return void
     */
    public function testFolderInfo(): void
    {
        $folder = substr(Arr::random(glob($this->root . '/*', GLOB_ONLYDIR)), strlen($this->root));
        $info = pathinfo($folder);

        $this->withoutMiddleware()
            ->post(route('file-browser.info'), ['path' => $folder])
            ->assertOk()
            ->assertJsonStructure($this->info_fields)
            ->assertJsonFragment([
                'path' => $info['dirname'],
                'isDir' => true,
                'basename' => $info['basename'],
                'filename' => $info['filename'],
                'ext' => '',
                'mime-type' => null,
                'size' => 4096,
                'type' => 'dir'
            ]);
    }

    /**
     * List folder content
     *
     * @return void
     */
    public function testFolderList(): void
    {
        $content = $this->requestResponse(
            $this->withoutMiddleware()
                ->post(route('file-browser.list'))
                ->assertOk()
                ->assertJsonMissing(['errors'])
        );
        foreach ($content as $value) {
            $this->assertFileExists($this->root . '/' . $value['basename']);
        }
    }

    /**
     * Rename file test
     *
     * @return void
     */
    public function testRenameFile(): void
    {
        $data = [
            'from' => '/' . uniqid() . '.txt',
            'to' => '/' . time() . '.txt'
        ];
        file_put_contents($this->root . $data['from'], '2');

        $this->withoutMiddleware()
            ->post(route('file-browser.move'), $data)
            ->assertOk()
            ->assertExactJson([
                'path' => $data['to']
            ]);

        $this->assertFileDoesNotExist($this->root . $data['from']);
        $this->assertFileExists($this->root . $data['to']);
    }

    /**
     * Move file
     *
     * @return void
     */
    public function testMoveFile(): void
    {
        $data = [
            'from' => '/' . uniqid() . '.txt',
            'to' => substr(Arr::random(glob($this->root . '/*', GLOB_ONLYDIR)), strlen($this->root))
        ];
        file_put_contents($this->root . '/' . $data['from'], '1');

        $goal_folder = $data['to'] . '/' . pathinfo($data['from'], PATHINFO_BASENAME);

        $this->withoutMiddleware()
            ->post(route('file-browser.move'), $data)
            ->assertOk()
            ->assertExactJson([
                'path' => $goal_folder
            ]);

        $this->assertFileDoesNotExist($this->root . $data['from']);
        $this->assertFileExists($this->root . $goal_folder);
    }

    /**
     * Move folder test
     *
     * @return void
     */
    public function testMoveFolder(): void
    {
        $data = array_combine(['from', 'to'], array_map(
            fn($file) => substr($file, strlen($this->root)),
            Arr::random(glob($this->root . '/*', GLOB_ONLYDIR), 2)
        ));

        $goal_folder = $data['to'] . '/' . pathinfo($data['from'], PATHINFO_BASENAME);

        $this->withoutMiddleware()
            ->post(route('file-browser.move'), $data)
            ->assertOk()
            ->assertExactJson([
                'path' => $goal_folder
            ]);

        $this->assertFileDoesNotExist($this->root . $data['from']);
        $this->assertFileExists($this->root . $goal_folder);
    }

    /**
     * Remove File test
     *
     * @return void
     */
    public function testRemoveFile(): void
    {
        $filename = uniqid() . '.txt';
        file_put_contents($this->root . '/' . $filename, '1');

        $this->withoutMiddleware()
            ->post(route('file-browser.remove'), [
                'path' => $filename
            ])
            ->assertNoContent();

        $this->assertFileDoesNotExist($this->root . '/' . $filename);
    }

    /**
     * Remove folder test
     *
     * @return void
     */
    public function testRemoveFolder(): void
    {
        $folder = Arr::random(glob($this->root . '/*', GLOB_ONLYDIR));

        $this->withoutMiddleware()
            ->post(route('file-browser.remove'), [
                'path' => substr($folder, strlen($this->root))
            ])
            ->assertNoContent();

        $this->assertFileDoesNotExist($folder);
    }

    /**
     * Search file test
     *
     * @return void
     */
    public function testSearchFile(): void
    {
        $filename = uniqid() . '.txt';
        file_put_contents($this->root . '/' . $filename, '1');

        $search = substr($filename, 1, mt_rand(5, 7));

        $content = $this->requestResponse(
            $this->withoutMiddleware()
                ->post(route('file-browser.search', $search), [
                    'path' => '/'
                ])
                ->assertOk()
        );

        $found = false;
        foreach ($content as $file) {
            if ($file['type'] === 'file') {
                $this->assertTrue(str_contains($file['filename'], $search));
                $found = true;
            }
        }
        $this->assertTrue($found);
    }

    /**
     * Search folder test
     *
     * @return void
     */
    public function testSearchFolder(): void
    {
        $search = null;
        foreach (Arr::random(glob($this->root . '/*', GLOB_ONLYDIR), 5) as $folder) {
            $inner = glob($folder . '/*', GLOB_ONLYDIR);
            if (!empty($inner)) {
                $search = pathinfo(substr(Arr::random($inner), strlen($this->root)), PATHINFO_BASENAME);
                break;
            }
        }

        if ($search) {
            $content = $this->requestResponse(
                $this->withoutMiddleware()
                    ->post(route('file-browser.search', $search), [
                        'path' => '/'
                    ])
                    ->assertOk()
            );

            $found = false;
            foreach ($content as $file) {
                if ($file['type'] == 'dir') {
                    $this->assertTrue(str_contains($file['filename'], $search));
                    $found = true;
                }
            }
            $this->assertTrue($found);
        }
    }

    /**
     * Getting folder size test
     *
     * @return void
     */
    public function testSize(): void
    {
        $this->withoutMiddleware()
            ->post(route('file-browser.size'), [
                'path' => substr(Arr::random(glob($this->root . '/*')), strlen($this->root))
            ])
            ->assertOk()
            ->assertJsonStructure(['size']);
    }

    /**
     * File upload test
     *
     * @return void
     */
    public function testFileUpload(): void
    {
        $file_name = $this->faker->jobTitle;
        $content = $this->requestResponse(
            $this->withoutMiddleware()
                ->post(route('file-browser.upload'), [
                    'file' => UploadedFile::fake()->create('document.pdf', 15),
                    'name' => $file_name,
                    'path' => '/'
                ])
                ->assertCreated()
                ->assertJsonStructure($this->info_fields)
        );

        $this->assertFileExists($this->root . '/' . $content['basename']);
    }

    /**
     * Convert request response to array
     *
     * @param TestResponse $response
     * @return array
     */
    protected function requestResponse(TestResponse $response): array
    {
        return json_decode($response->content(), 1);
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
}
