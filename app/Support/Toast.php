<?php

namespace App\Support;

use Illuminate\Support\Facades\Session;

final class Toast
{
    public static function success(string $content, int $duration = 4000): void
    {
        static::add($content, 'success', $duration);
    }

    public static function warning(string $content, int $duration = 4000): void
    {
        static::add($content, 'warning', $duration);
    }

    public static function error(string $content, int $duration = 4000): void
    {
        static::add($content, 'error', $duration);
    }

    public static function info(string $content, int $duration = 4000): void
    {
        static::add($content, 'info', $duration);
    }

    public static function add(string $content, string $type, int $duration = 4000): void
    {
        Session::flash('notify', [
            'content' => $content,
            'type' => $type,
            'duration' => $duration
        ]);
    }
}
