<?php

namespace App\Contracts\Plugin;

interface PluginInfo
{
    /**
     * @return string
     */
    public function name(): string;

    /**
     * @param string|null $name
     * @param mixed|null $default
     * @return array
     */
    public function getInfo(string $name = null, mixed $default = null): mixed;

    /**
     * @return array
     */
    public function getCommands(): array;

    /**
     * @return array
     */
    public function getListeners(): array;
}
