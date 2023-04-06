<?php

declare(strict_types=1);


namespace AshwoodsLightfoot\Moleman;


class BlackBag
{
    /** @var null|string  */
    protected $method = null;

    /** @var array|string  */
    protected $arguments = null;

    public function __construct(
        ?string $method = null,
        ?array $arguments = null
    ) {
        $this->setMethod($method);
        $this->setArguments($arguments);
    }

    public function getMethod(): ?string
    {
        return $this->method;
    }

    public function setMethod(?string $method): BlackBag
    {
        $doubleColonPieces = explode('::', $method);
        $this->method = end($doubleColonPieces);
        return $this;
    }

    public function getArguments(): ?array
    {
        return $this->arguments;
    }

    public function setArguments(?array $arguments): BlackBag
    {
        $this->arguments = $arguments;
        return $this;
    }
}
