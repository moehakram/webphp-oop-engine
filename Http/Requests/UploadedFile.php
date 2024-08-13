<?php
namespace MA\PHPQUICK\Http\Requests;

use MA\PHPQUICK\Contracts\HttpExceptionInterface as HttpException;

class UploadedFile extends \SplFileInfo
{
    private $tmpFilename;
    private $tmpSize;
    private $tmpMimeType;
    private $error;

    public function __construct(string $path, string $tmpFilename, int $tmpSize, string $tmpMimeType = '', int $error = UPLOAD_ERR_OK){
        parent::__construct($path);
        $this->tmpFilename = $tmpFilename;
        $this->tmpSize = $tmpSize;
        $this->tmpMimeType = $tmpMimeType;
        $this->error = $error;
    }

    public function getError() : int
    {
        return $this->error;
    }

    public function getMimeType() : string
    {
        $fInfo = new \finfo(FILEINFO_MIME_TYPE);

        return $fInfo->file($this->getPathname());
    }

    public function getTempExtension() : string
    {
        return pathinfo($this->tmpFilename, PATHINFO_EXTENSION);
    }

    public function getTempFilename() : string
    {
        return $this->tmpFilename;
    }

    public function getTempMimeType() : string
    {
        return $this->tmpMimeType;
    }

    public function getTempSize() : int
    {
        return $this->tmpSize;
    }

    public function hasErrors() : bool
    {
        return $this->error !== UPLOAD_ERR_OK;
    }

    public function move(string $targetDirectory, string $name = null)
    {
        if ($this->hasErrors()) {
            throw new HttpException(400, 'Cannot move file with errors');
        }

        if (!is_dir($targetDirectory)) {
            if (!mkdir($targetDirectory, 0777, true)) {
                throw new HttpException(400, 'Could not create directory ' . $targetDirectory);
            }
        } elseif (!is_writable($targetDirectory)) {
            throw new HttpException(400, $targetDirectory . ' is not writable');
        }

        $name = $name ?: $this->getBasename();
        $targetPath = rtrim($targetDirectory, '\\/') . '/' . $name;

        if (!$this->doMove($this->getPathname(), $targetPath)) {
            throw new HttpException(400, 'Could not move the uploaded file');
        }
    }

    protected function doMove(string $source, string $target) : bool
    {
        return @move_uploaded_file($source, $target);
    }
}