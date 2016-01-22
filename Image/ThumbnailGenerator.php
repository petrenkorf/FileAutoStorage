<?php

namespace Persistence\Image;

use Persistence\Image\HasImageInterface;
use Intervention\Image\ImageManager;

class ThumbnailGenerator
{
    protected $filename;

    protected $extension;

    protected $resolutions;

    protected $shouldResize;

    protected $hasThumb;

    protected $thumbnailFolder;

    public function store(HasImageInterface $model)
    {

    }

    public function loadImage()
    {
        $image = ImageManager::make();
    }

    public function resizeImage()
    {

    }

    public function store()
    {

    }

    public function extractExtension()
    {

    }

    public function generateFilename()
    {
        $this->filename = str_random(10);
    }
}
