<?php

declare(strict_types=1);

namespace AshwoodsLightfoot\Moleman;

class Spy
{
    /** @var BlackBag[]  */
    protected $bags = [];

    protected $plants = [];

    protected $spoofs = [];

    public const KEY_RETURN_VALUE = 'returnValue';
    public const KEY_REQUIRED_ARGS = 'requiredArguments';

    /**
     * Usage: e.g. return $spy->bag(__METHOD__, func_get_args()) ?? [];
     * @param string $method
     * @param array $arguments
     * @return mixed|null
     */
    public function bag(string $method, array $arguments)
    {
        $bag = new BlackBag($method, $arguments);
        $this->bags[] = $bag;
        if ($e = $this->plants[$bag->getMethod()] ?? null) {
            throw $e;
        }
        return $this->leverage($bag);
    }

    public function unspoof(string $method): self
    {
        unset($this->spoofs[$method]);
        return $this;
    }

    public function spoof(string $method, $returnValue, array $requiredArguments = []): self
    {
        if (!array_key_exists($method, $this->spoofs)) {
            $this->spoofs[$method] = [];
        }
        $this->spoofs[$method][] = [
            self::KEY_RETURN_VALUE => $returnValue,
            self::KEY_REQUIRED_ARGS => $requiredArguments,
        ];
        return $this;
    }

    public function sabotage(string $method, \Throwable $e): self
    {
        $this->plants[$method] = $e;
        return $this;
    }

    /**
     * @param BlackBag $targetBag
     * @return BlackBag[]
     */
    public function interrogate(BlackBag $targetBag): array
    {
        $found = [];
        foreach ($this->bags as $bag) {
            $methodMatches = true;
            if ($methodToMatch = $targetBag->getMethod()) {
                if ($methodToMatch !== $bag->getMethod()) {
                    $methodMatches = false;
                }
            }
            $argsMatch = true;
            if ($argsToMatch = $targetBag->getArguments()) {
                $args = $bag->getArguments();
                foreach ($argsToMatch as $i => $value) {
                    if (
                        !isset($args[$i])
                        || $args[$i] !== $value
                    ) {
                        $argsMatch = false;
                        break;
                    }
                }
            }
            if ($methodMatches && $argsMatch) {
                $found[] = $bag;
            }
        }
        return $found;
    }

    /**
     * @param BlackBag $bag
     * @return mixed|null
     */
    protected function leverage(BlackBag $bag)
    {
        $returnValueCandidates = $this->spoofs[$bag->getMethod()] ?? null;
        if (!$returnValueCandidates) {
            return null;
        }
        foreach ($returnValueCandidates as $candidate) {
            if (empty($candidate[self::KEY_REQUIRED_ARGS])) {
                return $candidate[self::KEY_RETURN_VALUE];
            }
            $bagArgs = $bag->getArguments();
            $argsMatch = true;
            foreach ($candidate[self::KEY_REQUIRED_ARGS] as $i => $requiredValue) {
                if (
                    !isset($bagArgs[$i])
                    || $bagArgs[$i] !== $requiredValue
                ) {
                    $argsMatch = false;
                    break;
                }
            }
            if ($argsMatch) {
                return $candidate[self::KEY_RETURN_VALUE];
            }
        }
        return null;
    }
}
