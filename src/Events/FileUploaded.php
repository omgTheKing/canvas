<?php

namespace Canvas\Events;

class FileUploaded{
    public function __construct(
        private string $path,
        private string $clientMimeType,
        private int $size
    ){}

    public function getPath(): string{
        return $this->path;
    }

    public function getClientMimeType(): string{
        return $this->clientMimeType;
    }

    public function getSize(): int{
        return $this->size;
    }
}
