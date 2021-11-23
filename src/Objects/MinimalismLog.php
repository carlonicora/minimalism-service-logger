<?php
namespace CarloNicora\Minimalism\Services\Logger\Objects;

use CarloNicora\Minimalism\Interfaces\Logger\Enums\LogLevel;

class MinimalismLog
{
    /**
     * MinimalismLog constructor.
     * @param LogLevel $level
     * @param string $message
     * @param string|null $domain
     * @param array|null $context
     */
    public function __construct(
        private LogLevel $level,
        private ?string $domain,
        private string $message,
        private ?array $context=null
    ) {
    }

    /**
     * @return LogLevel
     */
    public function getLevel(): LogLevel
    {
        return $this->level;
    }

    /**
     * @return string|null
     */
    public function getDomain(): ?string
    {
        return $this->domain;
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @return array|null
     */
    public function getContext(): ?array
    {
        return $this->context;
    }

    /**
     * @param string $request
     */
    public function addUriToContext(string $request): void
    {
        if ($this->context === null){
            $this->context = [];
        }

        $this->context['uri'] = $request;
    }
}