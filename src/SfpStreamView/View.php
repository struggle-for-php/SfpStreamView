<?php
/**
 * most of parts is borrowed from spindle-view
 * https://github.com/spindle/spindle-view
 * spindle/view is licensed under CC0-1.0 (Public Domain)
 */
namespace SfpStreamView;

use ArrayObject;
use SplStack;
use Psr\Http\Message\StreamableInterface;
use Phly\Http\Stream;

class View implements \IteratorAggregate
{
    protected $storage;
    protected $baseDir;
    protected $stack;

    /**
     * @param string $baseDir テンプレートの探索基準パスです。相対パスも指定できます。指定しなければinclude_pathから探索します。
     */
    public function __construct($baseDir = '', ArrayObject $vars = null)
    {
        $this->storage = $vars ?: new ArrayObject(array(), ArrayObject::ARRAY_AS_PROPS);
        $this->stack = new SplStack;
        $this->baseDir = rtrim($baseDir, \DIRECTORY_SEPARATOR);
    }

    /**
     * @return \ArrayIterator
     */
    function getIterator()
    {
        return $this->storage->getIterator();
    }

    /**
     * @param string|int $name
     * @return mixed
     */
    function __get($name)
    {
        return $this->storage[$name];
    }

    /**
     * @param string|int $name
     * @param mixed $value
     */
    function __set($name, $value)
    {
        $this->storage[$name] = $value;
    }

    /**
     * @param string|int $name
     * @return bool
     */
    function __isset($name)
    {
        return isset($this->storage[$name]);
    }

    /**
     * 描画するスクリプトファイルのパスを返します。
     * @return string
     */
    function __toString()
    {
        $fileName = $this->stack->pop();
        if ($this->baseDir) {
            return $this->baseDir . \DIRECTORY_SEPARATOR . $fileName;
        } else {
            return (string)$fileName;
        }
    }

    /**
     * return assigned vars as array
     * @return array
     */
    function toArray()
    {
        return (array)$this->storage;
    }

    /**
     * 配列で一気にview変数をセットします
     * @param array|\Traversable $array
     */
    function assign($array)
    {
        if (!is_array($array) && !($array instanceof \Traversable)) {
            throw new \InvalidArgumentException('$array must be array or Traversable.');
        }

        foreach ($array as $key => $value) {
            $this->storage[$key] = $value;
        }
    }

    /**
     * @param string $name
     * @param array $array
     */
    function append($name, $array)
    {
        $this->_merge($name, (array)$array, true);
    }

    /**
     * @param string $name
     * @param array $array
     */
    function prepend($name, $array)
    {
        $this->_merge($name, (array)$array, false);
    }

    /**
     * @param string $name
     * @param array $array
     * @param bool  $append
     */
    private function _merge($name, array $array, $append=true)
    {
        $s = $this->storage;
        if (isset($s[$name])) {
            if ($append) {
                $s[$name] = array_merge((array)$s[$name], $array);
            } else {
                $s[$name] = array_merge($array, (array)$s[$name]);
            }
        } else {
            $s[$name] = $array;
        }
    }

    public function render($template, $fp = null)
    {
        $fp = ($fp) ?: fopen('php://memory', 'wb+');

        $originStack = clone $this->stack;
        $this->stack->unshift(ltrim($template, \DIRECTORY_SEPARATOR));

        foreach ($this->storage as ${"\x00key"} => ${"\x00val"}) {
            $${"\x00key"} = ${"\x00val"};
        }

        ob_start(function($buffer) use (&$fp) {
            fwrite($fp, $buffer);
        });
        ob_implicit_flush(false);
        include (string)$this;
        ob_end_flush();

        $this->stack = $originStack;

        return $fp;
    }

    function content()
    {
        return $this->stack->pop();
    }

    /**
     * @param string $layoutFileName
     */
    function stackLayout($layoutFileName)
    {
        $this->stack[] = $layoutFileName;
    }
}
