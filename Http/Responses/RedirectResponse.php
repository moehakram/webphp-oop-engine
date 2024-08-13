<?php
namespace MA\PHPQUICK\Http\Responses;

class RedirectResponse extends Response
{

    public function __construct(string $targetUrl, int $statusCode = 302, array $headers = [])
    {
        if (empty($targetUrl)) {
            throw new \InvalidArgumentException('Invalid URL provided for redirect.');
        }

        parent::__construct('', $statusCode, $headers);

        $this->headers->set('Location', str_replace(['&amp;', '\n', '\r'], ['&', '', ''], $targetUrl));
    }

    public function with($key, $value = null)
    {
        session()->flash($key, $value);
        return $this;
    }

    public function withMessage(string $message, string $type = 'success'): self
    {
        $this->with('message', [
            'message' => $message,
            'type' => $type
        ]);
        return $this;
    }
    public function withErrors($errors): self
    {
        $this->with('errors', $errors);
        return $this;
    }

    public function withInputs(?array $input = null): self
    {
        $this->with('inputs',  is_null($input) ? (request()->post() ?: request()->query()) : $input);    
        return $this;
    }
}