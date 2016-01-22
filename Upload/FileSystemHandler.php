<?php

namespace Persistence\Upload;

use App; // Laravel -> Factory
use Illuminate\Filesystem\Filesystem; // Laravel
use Persistence\Upload\UploadableInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;


/**
 * @todo criar uma interface para facilitar o outro entre outros
 * frameworks OU implementar o armazenamento sem utilizar classes
 * acopladas de cada framework
 */

class FileSystemHandler
{
    protected $model;
    
    protected $filesystem;
    
    public function __construct()
    {
        $this->filesystem = App::make("Illuminate\Filesystem\Filesystem"); //new Filesystem();
    }

    /**
     * Armazena os arquivos enviados para o servidor
     * @param  UploadableInterface &$model [description]
     * @return [type]                      [description]
     */
    public function storeFiles(UploadableInterface &$model)
    {
        $newFiles = $this->getUploadedFiles($model);
        
        $originalAttr = $model->getOriginal();
        $uploadConfig = $model->getUploadableFields();


        foreach ($newFiles as $curFile) {
            //return dd($curFile);
            if($model->{$curFile} != null && in_array($curFile, $originalAttr) ) $this->removeIfExists($originalAttr[$curFile]);
            $file = $this->storeFile($model->{$curFile}, $uploadConfig[$curFile]);

            $model->{$curFile} = ($file != null) ? $file : $originalAttr[$curFile] ;
        }

        $this->checkForNullInput($model);
    }

    /**
     * Reseta attributos do modelo que foram sobrescritos por valores nulos
     * @param  [type] &$model [description]
     * @return [type]         [description]
     */
    private function checkForNullInput(&$model)
    {
        $originalAttr = $model->getOriginal();

        foreach($model->getAttributes() as $attr => $value) {
            if ($value == null) $model->{$attr} = $originalAttr[$attr]; 
        }
    }

    /**
     * Retorna um conjunto com o nome dos campos de arquivo enviados
     * para o servidor 
     * @param  [type] $model [description]
     * @return [type]        [description]
     */
    private function getUploadedFiles($model)
    {
        $originalAttr = $model->getOriginal();
        $fileAttr     = $model->getUploadableFields();

        foreach ($fileAttr as $index => $currentAttr) {
            if ($model->{$index} != null) {
                yield $index;
            }
        }
    }

    /**
     * Gera um nome para um arquivo enviado de acordo com as regras
     * providas no modelo
     * @param  [type] $field [description]
     * @return [type]        [description]
     */
    private function generateFilename($field)
    {
        return str_random(9).".".$field->getClientOriginalExtension();
    }
    /**
     * Apaga arquivo do sistema de arquivos
     * @param  [type] $currentFile [description]
     * @return [type]              [description]
     */
    public function removeIfExists($currentFile)
    {
        if ($this->filesystem->exists($currentFile)) {
            $this->filesystem->delete($currentFile);
        }
    }

    /**
     * Armazena arquivo no sistema de arquivos
     * @param  [type] $file   [description]
     * @param  [type] $config [description]
     * @return [type]         [description]
     */
    private function storeFile($file, $config)
    {
        $class = "Symfony\\Component\\HttpFoundation\\File\\UploadedFile";
        if (!$file instanceof $class) {
            return;
        }
        
        $filename = $this->generateFilename($file);
        $dir      = $config['directory'];
        
        $file->move($dir, $filename);
        return $dir.$filename;
    }
}