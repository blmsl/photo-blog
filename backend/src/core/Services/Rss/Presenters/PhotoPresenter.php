<?php

namespace Core\Services\Rss\Presenters;

use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Filesystem\Factory as Storage;
use Tooleks\Laravel\Presenter\Presenter;

/**
 * Class PhotoPresenter.
 *
 * @property string url
 * @property string title
 * @property string description
 * @property string file_url
 * @property string file_size
 * @property array categories
 * @property string published_date
 * @package Core\Services\Rss\Presenters
 */
class PhotoPresenter extends Presenter
{
    /**
     * The container implementation.
     *
     * @var Container
     */
    protected $container;

    /**
     * The storage implementation.
     *
     * @var Container
     */
    protected $storage;

    /**
     * PhotoPresenter constructor.
     *
     * @param Container $container
     * @param Storage $storage
     */
    public function __construct(Container $container, Storage $storage)
    {
        $this->container = $container;
        $this->storage = $storage;
    }

    /**
     * @inheritdoc
     */
    protected function getAttributesMap(): array
    {
        return [
            'url' => function (): ?string {
                return sprintf(config('format.frontend.url.photo_page'), $this->getWrappedModelAttribute('id'));
            },
            'title' => 'description',
            'description' => function (): ?string {
                $exif = $this->container
                    ->make(ExifPresenter::class)
                    ->setWrappedModel($this->getWrappedModelAttribute('exif'));

                if ($exif->manufacturer) {
                    $values[] = sprintf('%s: %s', trans('attributes.exif.manufacturer'), $exif->manufacturer);
                }

                if ($exif->model) {
                    $values[] = sprintf('%s: %s', trans('attributes.exif.model'), $exif->model);
                }

                if ($exif->exposure_time) {
                    $values[] = sprintf('%s: %s', trans('attributes.exif.exposure_time'), $exif->exposure_time);
                }

                if ($exif->aperture) {
                    $values[] = sprintf('%s: %s', trans('attributes.exif.aperture'), $exif->aperture);
                }

                if ($exif->iso) {
                    $values[] = sprintf('%s: %s', trans('attributes.exif.iso'), $exif->iso);
                }

                if ($exif->taken_at) {
                    $values[] = sprintf('%s: %s', trans('attributes.exif.taken_at'), $exif->taken_at);
                }

                return implode(', ', $values ?? []);
            },
            'file_url' => function (): ?string {
                $url = $this->storage->url($this->getWrappedModelAttribute('thumbnails')->first()->path) ?? null;
                return $url ? sprintf(config('format.storage.url.path'), $url) : null;
            },
            'file_size' => function (): ?string {
                return $this->storage->size($this->getWrappedModelAttribute('thumbnails')->first()->path);
            },
            'categories' => function (): array {
                return $this->getWrappedModelAttribute('tags')->pluck('value')->toArray() ?? [];
            },
            'published_date' => function (): ?string {
                return $this->getWrappedModelAttribute('created_at')->toAtomString();
            },
        ];
    }
}
