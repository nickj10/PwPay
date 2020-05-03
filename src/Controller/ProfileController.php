<?php

declare(strict_types=1);

namespace SallePW\SlimApp\Controller;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

final class ProfileController
{
    private const UPLOADS_DIR = __DIR__ . '/../../uploads';

    private const UNEXPECTED_ERROR = "An unexpected error occurred uploading the file '%s'...";
    private const INVALID_EXTENSION_ERROR = "The received file extension is not valid (Only png)";
    private const INVALID_SIZE_ERROR = "The received file size is too big. (Max. 1MB)";


    // We use this const to define the extensions that we are going to allow
    private const ALLOWED_EXTENSION = 'png';

    private const ALLOWED_DIMENSION = '400';

    private const ALLOWED_SIZE = '1000000';

    private ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function showProfile(Request $request, Response $response): Response
    {
        if (empty($_SESSION['user_id'])) {
            return $response->withHeader('Location', '/sign-in')->withStatus(403);
        }
        return $this->container->get('view')->render(
            $response,
            'profile.twig',
            [
                'session' => $_SESSION['user_id']
            ]
        );
    }
    public function profileAction(Request $request, Response $response): Response
    {
        $uploadedFile = $request->getUploadedFiles();
        $errors = [];

        /** @var UploadedFileInterface $uploadedFile */
        $file = $uploadedFile['files'];
        $name = $file->getClientFilename();
        $fileInfo = pathinfo($name);
        $format = strtolower($fileInfo['extension']);
        $size = $file->getSize();
        $tmpName = $_FILES['files']['tmp_name'];
        list($width, $height) = getimagesize($tmpName);
        if ($width > self::ALLOWED_DIMENSION || $height > self::ALLOWED_DIMENSION) {
            $errors['dimension'] = "Error dimension.";
        }      
        
        /*foreach ($uploadedFiles['files'] as $uploadedFile) {
            if ($uploadedFile->getError() !== UPLOAD_ERR_OK) {
                $errors[] = sprintf(
                    self::UNEXPECTED_ERROR,
                    $uploadedFile->getClientFilename()
                );
                continue;
            }
            $name = $uploadedFile->getClientFilename();
            $fileInfo = pathinfo($name);
            $format = strtolower($fileInfo['extension']);
            $size = $uploadedFile->getSize();

            $tmpName = $_FILES['files']['tmp_name'][0];
            list($width, $height) = getimagesize($tmpName);
            var_dump($width);

            //Check if photo is valid
            if ($format != self::ALLOWED_EXTENSION) {
                $errors['extension'] = self::INVALID_EXTENSION_ERROR;
            }
            if ($size >= self::ALLOWED_SIZE) {
                $errors['size'] = self::INVALID_SIZE_ERROR;
            }

            // We generate a custom name here instead of using the one coming form the form
            //$uploadedFile->moveTo(self::UPLOADS_DIR . DIRECTORY_SEPARATOR . $name);
        }*/

        return $this->container->get('view')->render($response, 'profile.twig', [
            'errors' => $errors,
        ]);
    }
}
