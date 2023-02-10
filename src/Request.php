<?php

declare(strict_types=1);

namespace Wherd\Http;

class Request
{
    public function get(string $key, mixed $default = null): mixed
    {
        return $_REQUEST[$key] ?? $default;
    }

    public function getFile(string $key): mixed
    {
        return $_FILES[$key] ?? null;
    }

    public function getCookie(string $key): mixed
    {
        return $_COOKIE[$key] ?? null;
    }

    public function getSession(string $key): mixed
    {
        return $_SESSION[$key] ?? null;
    }

    public function getMethod(): string
    {
        $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');

        if ('POST' === $method) {
            if (isset($_SERVER['X-HTTP-METHOD-OVERRIDE'])) {
                $method = $_SERVER['X-HTTP-METHOD-OVERRIDE'];
            } elseif (isset($_POST['_method'])) {
                $method = $_POST['_method'];
            }
        }

        return $method;
    }

    public function isMethod(string $method): bool
    {
        return $this->getMethod() === strtoupper($method);
    }

    public function getPath(): string
    {
        return $_SERVER['REQUEST_URI'] ?? '/';
    }

    public function getHeader(string $header, string $default = ''): string
    {
        return $_SERVER[$header] ?? $default;
    }

    public function getReferer(): string
    {
        return $_SERVER['HTTP_REFERER'] ?? '';
    }

    public function isSecured(): bool
    {
        return !empty($_SERVER['HTTPS']) && strcasecmp($_SERVER['HTTPS'], 'off');
    }

    public function isAjax(): bool
    {
        return 'XMLHttpRequest' === $this->getHeader('X-Requested-With');
    }

    public function getRemoteAddress(): string
    {
        return $_SERVER['HTTP_CLIENT_IP'] ?? $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '';
    }

    public function getRemoteHost(): string
    {
        return $_SERVER['REMOTE_HOST']
            ?? (gethostbyaddr($this->getRemoteAddress()) ?: '');
    }

    /** @param array<mixed> $langs */
    public function detectLanguage(array $langs): string
    {
        $header = $this->getHeader('HTTP_ACCEPT_LANGUAGE');

        if (!$header) {
            return '';
        }

        $s = strtolower($header);  // case insensitive
        $s = strtr($s, '_', '-');  // cs_CZ means cs-CZ
        rsort($langs);             // first more specific
        preg_match_all('#(' . implode('|', $langs) . ')(?:-[^\s,;=]+)?\s*(?:;\s*q=([0-9.]+))?#', $s, $matches);

        if (!$matches[0]) {
            return '';
        }

        $max = 0;
        $lang = '';

        foreach ($matches[1] as $key => $value) {
            $q = '' === $matches[2][$key] ? 1.0 : (float) $matches[2][$key];
            if ($q > $max) {
                $max = $q;
                $lang = $value;
            }
        }

        return $lang;
    }
}
