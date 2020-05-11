<?php

declare(strict_types=1);

namespace SallePW\SlimApp\Service;
use Imagick;

class ImageHandler {
    private const UPLOADS_DIR = '../public/uploads';
    private const UNEXPECTED_ERROR = "An unexpected error occurred uploading the file '%s'...";
    private const INVALID_EXTENSION_ERROR = "The received file extension '%s' is not valid (Only .png files)";
    private const INVALID_SIZE_ERROR = "Image size exceeds 1MB.";
    private const INVALID_DIMENSION_ERROR = "Image dimension should be within 400X400";
    private const ALLOWED_EXTENSION = 'png';
    private const ALLOWED_DIMENSION = '400';
    private const ALLOWED_SIZE = '1000000';

    private $errors = array();

    public function validateImage ($uploadedFile) {
        $file = $uploadedFile['files'];
        $name = $file->getClientFilename();
        $fileInfo = pathinfo($name);
        $format = strtolower($fileInfo['extension']);
        $size = $file->getSize();
        $tmpName = $_FILES['files']['tmp_name'];
        list($width, $height) = getimagesize($tmpName);

        if ($file->getError() !== UPLOAD_ERR_OK) {
            $this->errors['upload'] = sprintf(self::UNEXPECTED_ERROR, $name);
        }
        else {
            $basename = bin2hex(random_bytes(8));
            $filename = sprintf('%s.%0.8s', $basename, $format);
            //Checks if extension is valid
            if ($format != self::ALLOWED_EXTENSION) {
                $this->errors['extension'] = sprintf(self::INVALID_EXTENSION_ERROR, $format);
            }
            //Checks if the size is valid
            if ($size >= self::ALLOWED_SIZE) {
                $this->errors['size'] = self::INVALID_SIZE_ERROR;
            }
            //If extension and size is valid, we check for the dimension
            if ($format == self::ALLOWED_EXTENSION && $size <= self::ALLOWED_SIZE) {
                //If dimension is too big, we crop the image to 400x400
                if ($width > self::ALLOWED_DIMENSION || $height > self::ALLOWED_DIMENSION) {
                    $cropImage = new Imagick($tmpName);
                    $cropImage->cropThumbnailImage(400,400);
                    $cropImage->writeImage(self::UPLOADS_DIR . DIRECTORY_SEPARATOR . $filename);
                }
                else {
                    //If dimension is fine we generate a unique name and store it to a folder
                    $file->moveTo(self::UPLOADS_DIR . DIRECTORY_SEPARATOR . $filename);
                }
            }
        }
        return $this->errors;

    }



}