<?php

namespace Console\Commands;

use Core\Managers\Photo\Contracts\PhotoManager;
use Core\Models\Photo;
use Core\Models\Thumbnail;
use Illuminate\Config\Repository as Config;
use Illuminate\Console\Command;
use Illuminate\Contracts\Filesystem\Factory as Storage;

/**
 * Class DeleteUnusedObjectsFromPhotoStorage.
 *
 * @package Console\Commands
 */
class DeleteUnusedObjectsFromPhotoStorage extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'delete:unused_objects_from_photo_storage';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete unused objects from photo storage';

    /**
     * @var Config
     */
    private $config;

    /**
     * @var Storage
     */
    private $storage;

    /**
     * @var PhotoManager
     */
    private $photoManager;

    /**
     * DeleteUnusedObjectsFromPhotoStorage constructor.
     *
     * @param Config $config
     * @param Storage $storage
     * @param PhotoManager $photoManager
     */
    public function __construct(Config $config, Storage $storage, PhotoManager $photoManager)
    {
        parent::__construct();

        $this->config = $config;
        $this->storage = $storage;
        $this->photoManager = $photoManager;
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle(): void
    {
        foreach ($this->getDirectoriesToDelete() as $directory) {
            $this->comment("Deleting [directory:'{$directory}'] ...");
            $this->storage->deleteDirectory($directory);
        }

        foreach ($this->getFilesToDelete() as $file) {
            $this->comment("Deleting [file:'{$file}'] ...");
            $this->storage->delete($file);
        }
    }

    /**
     * Get directories to delete.
     *
     * @return array
     */
    protected function getDirectoriesToDelete(): array
    {
        $directories = array_diff($this->getAllDirectoriesFromStorage(), $this->getAllDirectoriesFromDataProvider());

        return array_filter(array_unique($directories), 'boolval');
    }

    /**
     * Get files to delete.
     *
     * @return array
     */
    protected function getFilesToDelete(): array
    {
        $files = array_diff($this->getAllFilesFromStorage(), $this->getAllFilesFromDataProvider());

        return array_filter(array_unique($files), 'boolval');
    }

    /**
     * Get all directories from a storage.
     *
     * @return array
     */
    protected function getAllDirectoriesFromStorage(): array
    {
        return $this->storage->allDirectories($this->config->get('main.storage.path.photos'));
    }

    /**
     * Get all files from a storage.
     *
     * @return array
     */
    protected function getAllFilesFromStorage(): array
    {
        return $this->storage->allFiles($this->config->get('main.storage.path.photos'));
    }

    /**
     * Get all directories from a data provider.
     *
     * @return array
     */
    protected function getAllDirectoriesFromDataProvider(): array
    {
        $this->photoManager->each(function (Photo $photo) use (&$directories) {
            $directories[] = dirname($photo->path);
        });

        return $directories ?? [];
    }

    /**
     * Get all files from a data provider.
     *
     * @return array
     */
    protected function getAllFilesFromDataProvider(): array
    {
        $this->photoManager->each(function (Photo $photo) use (&$files) {
            $files[] = $photo->path;
            $photo->thumbnails->each(function (Thumbnail $thumbnail) use (&$files) {
                $files[] = $thumbnail->path;
            });
        });

        return $files ?? [];
    }
}
