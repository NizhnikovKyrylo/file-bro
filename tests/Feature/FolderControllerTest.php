<?php

namespace Tests\Feature;

use App\Http\Controllers\FolderController;
use Faker\Factory as Faker;
use Illuminate\Support\Str;
use Tests\TestCase;

class FolderControllerTest extends TestCase
{
    public function __construct(?string $name = null, array $data = [], $dataName = '', protected $faker = null)
    {
        parent::__construct($name, $data, $dataName);

        $this->faker = Faker::create();
    }

    /**
     * Seed some file structure
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $folders = array_merge($this->faker->words, [
            'inner_1/inner_2',
        ]);

        foreach ($folders as $folder) {
            $path = config('file-browser.entry') . DIRECTORY_SEPARATOR . $folder;
            if (!file_exists($path)) {
                mkdir($path, 0755, true);
            }
        }
        file_put_contents(config('file-browser.entry') . '/inner_1/inner_2/test.txt', 'some data');
    }

    /**
     * Test get folder list method
     *
     * @return void
     */
    public function testFolderList(): void
    {
        $response = $this->withoutMiddleware()
            ->get(route('file-browser.folder.list'))
            ->assertOk();

        $content = json_decode($response->content(), true);
        foreach ($content as $value) {
            $this->assertFileExists(Str::finish(config('file-browser.entry'), '/') . $value['basename']);
        }
    }

    /**
     * Folder properties
     *
     * @return void
     */
    public function testFolderProperties(): void
    {
        $this->withoutMiddleware()
            ->get(route('file-browser.folder.properties') . '?path=/')
            ->assertOk()
            ->assertJsonStructure([
                'files',
                'folders',
                'size',
                'path',
                'ctime',
                'mtime',
                'atime',
                'type',
                'filename'
            ]);
    }

    /**
     * Test search folder
     *
     * @return void
     */
    public function testSearchFolder(): void
    {
        $parent_folder = uniqid() . '/inner_1/';
        $folder = uniqid();
        mkdir(config('file-browser.entry') . '/' . $parent_folder . $folder, 0755, true);

        $response = $this->withoutMiddleware()
            ->get(route('file-browser.folder.search', $folder) . '?path=/')
            ->assertOk();

        $content = json_decode($response->content());
        $this->assertTrue($content[0]->path == Str::start($parent_folder, '/'));
        $this->assertTrue($content[0]->basename == $folder);
    }

    /**
     * Folder size test
     *
     * @return void
     */
    public function testFolderSize(): void
    {
        $this->withoutMiddleware()
            ->get(route('file-browser.folder.size') . '?path=/')
            ->assertOk()
            ->assertJsonStructure(['size']);
    }

    /**
     * Create folder test
     *
     * @return void
     */
    public function testCreateFolder(): void
    {
        // Attempt to create directory in an entry folder
        $folder = 'create_' . uniqid();

        $response = $this->withoutMiddleware()
            ->post(route('file-browser.folder.create'), ['path' => $folder])
            ->assertCreated()
            ->assertExactJson(['path' => '/' . $folder]);

        $content = json_decode($response->content());
        $this->assertFileExists(config('file-browser.entry') . $content->path);

        // Attempt to create directory in a sub folder
        $sub_folder = 'create_' . uniqid();
        $response = $this->withoutMiddleware()
            ->post(route('file-browser.folder.create'), ['path' => $sub_folder . '/' . $folder])
            ->assertCreated()
            ->assertExactJson(['path' => '/' . $sub_folder . '/' . $folder]);

        $content = json_decode($response->content());
        $this->assertFileExists(config('file-browser.entry') . $content->path);
    }

    /**
     * Copy folder validation test
     *
     * @return void
     */
    public function testCopyFolderValidation(): void
    {
        foreach ($this->copyTestCases() as $test_case) {
            $this->withoutMiddleware()
                ->post(route('file-browser.folder.copy'), $test_case['data'])
                ->assertStatus($test_case['status'])
                ->assertExactJson($test_case['messages']);
        }
    }

    /**
     * Test Recursive folder copy
     *
     * @return void
     */
    public function testCopyFolder()
    {
        $source_folder = '/inner_1/inner_2';
        $destination_folder = '/new_copy_' . uniqid();

        $this->withoutMiddleware()
            ->post(route('file-browser.folder.copy'), [
                'from' => $source_folder,
                'to' => $destination_folder
            ])
            ->assertCreated()
            ->assertExactJson(['path' => $destination_folder]);

        $this->assertFileExists(config('file-browser.entry') . $destination_folder);

        foreach (glob(config('file-browser.entry') . $source_folder . '/*') as $file) {
            $this->assertFileExists($file);
        }
    }

    /**
     * Rename folder test
     *
     * @return void
     */
    public function testRenameFolder(): void
    {
        $target_folder = 'test_' . uniqid();

        mkdir(config('file-browser.entry') . '/' . $target_folder, 0755, true);

        $r = $this->withoutMiddleware()
            ->post(route('file-browser.folder.rename'), [
                'from' => '/' . $target_folder . '/',
                'to' => 'renamed_' . uniqid()
            ])
            ->assertOk()
            ->assertJsonStructure(['path']);

        $content = json_decode($r->content(), 1);
        $this->assertFileExists(config('file-browser.entry') . $content['path']);
        $this->assertFileDoesNotExist(config('file-browser.entry') . $target_folder);
    }

    /**
     * Move folder test
     *
     * @return void
     */
    public function testMoveFolder(): void
    {
        $source_folder = 'move_' . uniqid();
        $target_folder = 'new_move_' . uniqid();
        mkdir(config('file-browser.entry') . '/' . $source_folder, 0755, true);
        mkdir(config('file-browser.entry') . '/' . $target_folder, 0755, true);

        $this->withoutMiddleware()
            ->post(route('file-browser.folder.move'), [
                'from' => $source_folder,
                'to' => $target_folder
            ])
            ->assertOk()
            ->assertExactJson(['path' => '/' . $target_folder . '/' . $source_folder]);

        $this->assertFileDoesNotExist(config('file-browser.entry') . '/' . $source_folder);
        $this->assertFileExists(config('file-browser.entry') . '/' . $target_folder . '/' . $source_folder);
    }

    /**
     * Remove folder test
     *
     * @return void
     */
    public function testRemoveFolder(): void
    {
        $folders = glob(config('file-browser.entry') . '/*');

        foreach ($folders as $folder) {
            if (str_contains($folder, '/new_') || str_contains($folder, 'create_')) {
                $this->withoutMiddleware()
                    ->post(route('file-browser.folder.remove'), ['path' => pathinfo($folder, PATHINFO_FILENAME)])
                    ->assertNoContent();
                $this->assertFileDoesNotExist($folder);
            }
        }
    }

    /**
     * Copy and move folder tests cases
     *
     * @return array[]
     */
    protected function copyTestCases(): array
    {
        return [
            [
                'data' => [],
                'messages' => [
                    'errors' => [
                        'from' => ['The from field is required.'],
                        'to' => ['The to field is required.']
                    ],
                ],
                'status' => 422
            ],
            [
                'data' => ['from' => false, 'to' => false],
                'messages' => [
                    'errors' => [
                        'from' => ['The from field must be a string.'],
                        'to' => ['The to field must be a string.']
                    ]
                ],
                'status' => 422
            ],
            [
                'data' => ['from' => Str::random(), 'to' => Str::random()],
                'messages' => ['errors' => [FolderController::FILE_BROWSER_NOT_EXIST]],
                'status' => 404
            ]
        ];
    }
}
