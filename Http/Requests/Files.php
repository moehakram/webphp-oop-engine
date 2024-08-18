<?php
namespace MA\PHPQUICK\Http\Requests;

use MA\PHPQUICK\Collection;

class Files extends Collection
{
    public function set(string $name, $value): self
    {
        $this->items[$name] = new UploadedFile(
            $value['tmp_name'],
            $value['name'],
            $value['size'],
            $value['type'],
            $value['error']
        );
        return $this;
    }

    public function get(string $name, $default = null): UploadedFile
    {
        return parent::get($name, $default);
    }
}