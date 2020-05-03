<?php

declare(strict_types=1);

namespace SallePW\SlimApp\Controller;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

final class FileController
{
    private const UPLOADS_DIR = __DIR__ . '/../../uploads';

    private const UNEXPECTED_ERROR = "An unexpected error occurred uploading the file '%s'...";

    private const INVALID_EXTENSION_ERROR = "The received file extension '%s' is not valid";

    // We use this const to define the extensions that we are going to allow
    private const ALLOWED_EXTENSION = 'png';

    private const ALLOWED_DIMENSION = '400';

    private const ALLOWED_SIZE = '1000000';

    private ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function showFileFormAction(Request $request, Response $response): Response
    {
        return $this->container->get('view')->render(
            $response,
            'upload.twig',
            []
        );
    }
    public function uploadFileAction(Request $request, Response $response): Response
    {
        $uploadedFiles = $request->getUploadedFiles();

        $errors = [];

        /** @var UploadedFileInterface $uploadedFile */
        foreach ($uploadedFiles['files'] as $uploadedFile) {
            if ($uploadedFile->getError() !== UPLOAD_ERR_OK) {
                $errors[] = sprintf(
                    self::UNEXPECTED_ERROR,
                    $uploadedFile->getClientFilename()
                );
                continue;
            }

            $name = $uploadedFile->getClientFilename();

            $fileInfo = pathinfo($name);

            $format = $fileInfo['extension'];
            $size = $uploadedFile->getSize();

            if ($format == self::ALLOWED_EXTENSION) {
                $errors[] = sprintf(self::INVALID_EXTENSION_ERROR, $format);
                continue;
            }

            if ($size >= self::ALLOWED_SIZE) {
                echo "Size is too big";
            }

            // We generate a custom name here instead of using the one coming form the form
            //$uploadedFile->moveTo(self::UPLOADS_DIR . DIRECTORY_SEPARATOR . $name);
        }

        return $this->container->get('view')->render($response, 'upload.twig', [
            'errors' => $errors,
        ]);
    }
}
