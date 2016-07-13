<?php

namespace Persistence\Upload;

use App;
use Illuminate\Filesystem\Filesystem;


class FileSystemHandler
{
    protected $model;

    protected $filesystem;

    protected $uploadedFiles;

    protected $uploadConfig;

    const FILENAME_SIZE = 9;

    public function __construct()
    {
        $this->filesystem = App::make("Illuminate\Filesystem\Filesystem");
    }

    public function storeFiles(UploadableInterface &$model)
    {
        $this->model = &$model;

        $this->uploadConfig = $model->getUploadableFields();
        $this->uploadedFiles = $this->getUploadedFilesFromModel();

        $this->revertNullAttributesToOriginal();
        $this->storeUploadedFiles();
    }

    private function getUploadedFilesFromModel()
    {
        $uploadableAttributes = $this->model->getUploadableFields();

        $collection = [];

        foreach ($uploadableAttributes as $attributeName => $content) {
            if (!$this->isModelAttributeNull($attributeName)) {
                $collection[$attributeName] = $this->model->{$attributeName};
            }
        }

        return $collection;
    }

    private function revertNullAttributesToOriginal()
    {
        $attributes = $this->model->getAttributes();

        foreach ($attributes as $attributeName => $value) {
            if ($this->isModelAttributeNull($attributeName)) {
                $this->model->{$attributeName} = $this->model->getOriginal($attributeName);
            }
        }
    }

    private function isModelAttributeNull($attributeName)
    {
        return $this->model->{$attributeName} == null;
    }

    private function isModelOriginalAttributeNull($attributeName)
    {
        return $this->model->getOriginal($attributeName) == null;
    }

    private function storeUploadedFiles()
    {
        foreach ($this->uploadedFiles as $attributeName => $file) {
            $storedOldFile = $this->model->getOriginal($attributeName);

            if (!$this->isModelOriginalAttributeNull($attributeName)) {
                $this->removeIfExists($storedOldFile);
            }

            $this->model->{$attributeName} = $this->storeFile($attributeName);
        }
    }

    private function removeIfExists($filepath)
    {
        if ($this->filesystem->exists($filepath)) {
            $this->filesystem->delete($filepath);
        }
    }

    private function generateFilename($attribute)
    {
        return str_random(self::FILENAME_SIZE).'.'.$this->model->{$attribute}->getClientOriginalExtension();
    }

    private function storeFile($attributeName)
    {
        $file = $this->model->{$attributeName};

        if (!$this->isUploadedFileValidInstance($file)) {
            return;
        }

        $filename = $this->generateFilename($attributeName);
        $dir = $this->uploadConfig[$attributeName]['directory'];

        $this->model->{$attributeName}->move($dir, $filename);

        return $dir.$filename;
    }

    private function isUploadedFileValidInstance($file)
    {
        $class = 'Symfony\Component\HttpFoundation\File\UploadedFile';

        return (bool) ($file instanceof $class);
    }

    public function removeAllFiles(UploadableInterface &$model)
    {
        $uploadableAttributes = $model->getUploadableFields();

        foreach ($uploadableAttributes as $attribute => $value) {
            $this->removeIfExists($model->{$attribute});
        }
    }
}
