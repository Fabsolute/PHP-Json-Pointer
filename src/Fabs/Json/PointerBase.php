<?php
/**
 * Created by PhpStorm.
 * User: ahmetturk
 * Date: 06/06/2017
 * Time: 17:41
 */

namespace Fabs\Json;


abstract class PointerBase
{
    protected $root = null;

    protected function getTokens($pointer)
    {
        if ($pointer === '') {
            return [];
        }

        if ($pointer[0] !== '/') {
            throw new \InvalidArgumentException('invalid json pointer ' . $pointer);
        }

        $tokens = array_map(function ($path_element) {
            return str_replace('~1', '/', str_replace('~0', '~', $path_element));
        }, explode('/', substr($pointer, 1)));

        if (count($tokens) === 1 && $tokens[0] === '') {
            return [];
        }
        return $tokens;
    }

    /**
     * @param string $pointer
     * @return bool
     */
    public function has($pointer)
    {
        try {
            $this->get($pointer);
        } catch (\Exception $e) {
            return false;
        }
        return true;
    }

    /**
     * @param string $pointer
     * @return mixed
     */
    public abstract function get($pointer);

    /**
     * @param string $pointer
     * @param mixed $value
     */
    public abstract function set($pointer, $value);

    /**
     * @param string $pointer
     */
    public abstract function remove($pointer);

    /**
     * @return mixed
     */
    public function getRoot()
    {
        return $this->root;
    }

    public function setRoot($root)
    {
        $this->root = $root;
    }
}