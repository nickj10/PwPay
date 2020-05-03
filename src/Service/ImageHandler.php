<?php

declare(strict_types=1);

namespace SallePW\SlimApp\Service;

class ImageHandler {
    private const UPLOADS_DIR = __DIR__ . '/../../uploads';
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
            if ($format != self::ALLOWED_EXTENSION) {
                $this->errors['extension'] = sprintf(self::INVALID_EXTENSION_ERROR, $format);
            }
            if ($size >= self::ALLOWED_SIZE) {
                $this->errors['size'] = self::INVALID_SIZE_ERROR;
            }
            if ($width > self::ALLOWED_DIMENSION || $height > self::ALLOWED_DIMENSION) {
                $this->errors['dimension'] = self::INVALID_DIMENSION_ERROR;

            }
            //TODO: Crop part, upload, and file name
            // We generate a custom name here instead of using the one coming form the form
            //$uploadedFile->moveTo(self::UPLOADS_DIR . DIRECTORY_SEPARATOR . $name);
        }
        return $this->errors;

    }



}