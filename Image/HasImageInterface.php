<?php

namespace Persistence\Image;

interface HasImageInterface
{
    public function getThumbResolutions();

    public function getImageFolder();

    public function isResizable();

    public function hasThumbnail();

    public function getThumbnailFolder();
}
