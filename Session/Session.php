<?php
namespace MA\PHPQUICK\Session;

use MA\PHPQUICK\Collection;

class Session extends Collection
{
    const FLASH = '_FLASH';

    public function __construct()
    {
        session_start();
        parent::__construct($_SESSION);
        $this->initializeFlashMessages();
    }

    private function initializeFlashMessages(): void
    {
        $flashMessages = $this->get(self::FLASH, []);
        foreach ($flashMessages as &$flashMessage) {
            $flashMessage['is_remove'] = true;
        }
        $this->set(self::FLASH, $flashMessages);
    }

    public function setFlash(string $key, $value): self
    {
        $flashMessages = $this->get(self::FLASH, []);
        $flashMessages[$key] = [
            'is_remove' => false,
            'value' => $value
        ];
        $this->set(self::FLASH, $flashMessages);
        return $this;
    }

    public function getFlash(string $key)
    {
        $flashMessages = $this->get(self::FLASH, []);
        return $flashMessages[$key]['value'] ?? [];
    }

    private function removeFlashMessages(): void
    {
        $flashMessages = array_filter($this->get(self::FLASH, []), fn($msg) => !$msg['is_remove']);
        $this->set(self::FLASH, $flashMessages);
    }

    public function __destruct()
    {
        $this->removeFlashMessages();
        $_SESSION = $this->getAll();
    }

    public function flash($key, $value = null): void
    {
        $keys = is_array($key) ? $key : [$key => $value];

        foreach ($keys as $k => $v) {
            $this->setFlash($k, $v);
        }
    }
}
