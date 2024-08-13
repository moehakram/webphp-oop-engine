<?php
namespace MA\PHPQUICK\Session;

use MA\PHPQUICK\Collection;

class Session extends Collection
{
    const FLASH = 'FLASH_MESSAGES';

    public function __construct()
    {
        session_start();
        parent::__construct($_SESSION);
        $flashMessages = $this->get(self::FLASH, []);
        foreach ($flashMessages as $key => &$flashMessage) {
            $flashMessage['is_remove'] = true;
        }
        $this->set(self::FLASH, $flashMessages);
    }

    public function setFlash(string $key, $value)
    {
        $flashMessages = $this->get(self::FLASH, []);
        $flashMessages[$key] = [
            'is_remove' => false,
            'value' => $value
        ];
        $this->set(self::FLASH, $flashMessages);
    }

    public function getFlash(string $key)
    {
        $flashMessages = $this->get(self::FLASH, []);
        return $flashMessages[$key]['value'] ?? [];
    }

    private function removeFlashMessages()
    {
        $flashMessages = $this->get(self::FLASH, []);
        foreach ($flashMessages as $key => $flashMessage) {
            if ($flashMessage['is_remove']) {
                unset($flashMessages[$key]);
            }
        }
        $this->set(self::FLASH, $flashMessages);
    }

    public function __destruct()
    {
        $this->removeFlashMessages();
        $_SESSION = $this->getAll();
    }

    public function flash($key, $value = null)
    {
        $keys = is_array($key) ? $key : [$key => $value];

        foreach ($keys as $k => $v) {
            $this->setFlash($k, $v);
        }
    }
}
