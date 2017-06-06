<?php

namespace Fabs\Json;


class ArrayPointer extends PointerBase
{
    /**
     * ArrayPointer constructor.
     * @param array|string $subject
     */
    public function __construct($subject)
    {
        if (is_string($subject)) {
            $this->root = json_decode($subject, true, JSON_UNESCAPED_UNICODE);
            if (json_last_error() == JSON_ERROR_NONE) {
                return;
            }
        }

        if (is_array($subject)) {
            $this->root = $subject;
            return;
        }

        throw new \InvalidArgumentException('content must be json string or array');
    }

    /**
     * @param string $pointer
     * @return mixed
     */
    public function get($pointer)
    {
        $tokens = $this->getTokens($pointer);
        return $this->getInternal($tokens);
    }

    /**
     * @param string $pointer
     * @param mixed $value
     * @throws \Exception
     */
    public function set($pointer, $value)
    {
        $tokens = $this->getTokens($pointer);
        if (count($tokens) === 0) {
            throw new \Exception('can not set the root');
        }
        $this->root = $this->setInternal($this->root, $tokens, $value);
    }

    /**
     * @param string $pointer
     * @throws \Exception
     */
    public function remove($pointer)
    {
        $tokens = $this->getTokens($pointer);
        if (count($tokens) === 0) {
            throw new \Exception('can not remove the root');
        }
        $this->root = $this->removeInternal($this->root, $tokens);
    }

    private function getInternal($tokens)
    {
        $target = $this->root;
        if (count($tokens) > 0) {
            foreach ($tokens as $token) {
                if (is_array($target) && array_key_exists($token, $target)) {
                    $target = $target[$token];
                } else {
                    throw new \InvalidArgumentException('pointer does not exists');
                }
            }
            return $target;
        }

        return $target;
    }

    private function setInternal($root, $tokens, $value)
    {
        if (count($tokens) > 0) {
            if (is_array($root)) {
                $token = array_shift($tokens);
                if ($token === '-') {
                    $token = count($root);
                }

                if (!array_key_exists($token, $root)) {
                    $root[$token] = [];
                }
                $root[$token] = $this->setInternal($root[$token], $tokens, $value);
            } else {
                throw new \InvalidArgumentException('invalid json pointer');
            }
        } else {
            return $value;
        }
        return $root;
    }

    private function removeInternal($root, $tokens)
    {
        if (count($tokens) > 0) {
            if (is_array($root)) {
                if (!array_key_exists($tokens[0], $root)) {
                    throw new \InvalidArgumentException('invalid json pointer');
                }
                $token = array_shift($tokens);
                if (count($tokens) > 0) {
                    $root[$token] = $this->removeInternal($root[$token], $tokens);
                } else {
                    unset($root[$token]);
                }
            } else {
                throw new \InvalidArgumentException('invalid json pointer');
            }
        }
        return $root;
    }
}