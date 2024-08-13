<?php
namespace MA\PHPQUICK\Http\Responses;

class JsonResponse extends Response
{
    public function __construct($content = [], int $statusCode = 200, array $headers = [])
    {
        parent::__construct($content, $statusCode, $headers);

        $this->headers->set('Content-Type', ResponseHeaders::CONTENT_TYPE_JSON);
    }

    public function setContent($content) : self
    {
        if ($content instanceof \ArrayObject) {
            $content = $content->getArrayCopy();
        }

        $json = json_encode($content, JSON_THROW_ON_ERROR);

        parent::setContent($json);

        return $this;
    }
}