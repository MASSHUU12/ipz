<?php

namespace App\Chatbot;

interface ModuleInterface
{
    /**
     * Return an array of pattern entries using the same schema the controller expects.
     *
     * Example:
     * [
     *   [
     *     "pattern" => "/^time(?: in (?P<tz>.+))?$/i",
     *     "responses" => [],
     *     "callback" => [\App\Chatbot\Modules\TimeModule::class, 'timeCallback'],
     *     "severity" => "low",
     *     "priority" => 0,
     *     "enabled" => true,
     *   ],
     *   ...
     * ]
     *
     * Callbacks may be any PHP callable (static method arrays, closure, invokable objects, etc).
     *
     * @return array
     */
    public static function getPatterns(): array;

    /**
     * Return an integer mtime representing the last modification time of the module
     * (used for controller-level caching). Typically return the filemtime of the
     * module file or 0 if unknown.
     *
     * @return int
     */
    public static function getMTime(): int;
}
