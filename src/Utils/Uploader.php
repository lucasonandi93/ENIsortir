<?php

namespace App\Utils;

use Symfony\Component\HttpFoundation\File\UploadedFile;

class Uploader
{

    public function upload(UploadedFile $file, string $directory, string $name = ""){

        //création d'un nouveau nom
        $newFileName = $name . "-" . uniqid() . "." . $file->guessExtension();
        //copy du fichier dans le répertoire de sauvegarde en le renommant
        $file->move($directory, $newFileName);

        return $newFileName;
    }

}