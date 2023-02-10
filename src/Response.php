<?php

declare(strict_types=1);

namespace Wherd\Http;

class Response
{
    /** @var array<array<mixed>> */
    protected array $headers = [];

    protected int $statusCode = 200;
    protected string $content = '';

    public static function redirect(string $uri, int $status=302): self
    {
        $response = new self();
        $response->header('Location: ' . $uri, true, $status);
        return $response;
    }

    public function header(string $header, bool $replace=false, int $code=0): void
    {
        $this->headers[] = [$header, $replace, $code];
    }

    protected function sendHeaders(): void
    {
        if (headers_sent()) {
            throw new \RuntimeException('Headers already sent.');
        }

        foreach ($this->headers as $header) {
            header(...$header);
        }
    }

    public function setStatusCode(int $code): void
    {
        $this->statusCode = $code;
    }

    /** @param mixed $value */
    public function setSession(string $key, $value): void
    {
        $_SESSION[$key] = $value;
    }

    public function setContent(string $content): void
    {
        $this->content = $content;
    }

    public function html(string $content): void
    {
        $this->headers[] = ['Content-Type: text/html; charset=UTF-8'];
        $this->content = $content;
    }

    public function text(string $content): void
    {
        $this->headers[] = ['Content-Type: plain/text; charset=UTF-8'];
        $this->content = $content;
    }

    public function js(string $content): void
    {
        $this->headers[] = ['Content-Type: application/javascript; charset=UTF-8'];
        $this->content = $content;
    }

    public function css(string $content): void
    {
        $this->headers[] = ['Content-Type: text/css; charset=UTF-8'];
        $this->content = $content;
    }

    /** @param mixed $data */
    public function json($data): void
    {
        $this->headers[] = ['Content-Type: application/json; charset=UTF-8'];
        $this->content = json_encode($data) ?: '';
    }

    public function send(): void
    {
        http_response_code($this->statusCode);
        $this->sendHeaders();
        echo $this->content;
    }
}
