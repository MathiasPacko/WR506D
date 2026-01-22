<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\MediaObject;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

final class MediaObjectProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly ProcessorInterface $persistProcessor
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        $request = $context['request'] ?? null;

        if ($request instanceof Request) {
            $uploadedFile = $request->files->get('file');

            if (!$uploadedFile) {
                throw new BadRequestHttpException('"file" is required');
            }

            $mediaObject = new MediaObject();
            $mediaObject->file = $uploadedFile;

            return $this->persistProcessor->process($mediaObject, $operation, $uriVariables, $context);
        }

        return $this->persistProcessor->process($data, $operation, $uriVariables, $context);
    }
}
