<?php
/**
 * most of parts is borrowed from spindle-view
 * https://github.com/spindle/spindle-view
 *
 * spindle/view
 * @license CC0-1.0 (Public Domain)
 */
namespace SfpStreamView;

use ArrayObject;
use SplStack;
use Psr\Http\Message\StreamableInterface;
use Phly\Http\Stream;

class View implements \IteratorAggregate
{
    protected $storage;
    protected $basePath;
    protected $stack;

    /**
     * @param string $fileName 描画したいテンプレートのファイル名を指定します
     * @param string $basePath テンプレートの探索基準パスです。相対パスも指定できます。指定しなければinclude_pathから探索します。
     * @param ArrayObject $arr
     */
    public function __construct($fileName, $basePath = '', ArrayObject $arr = null)
    {
        $this->storage = $arr ?: new ArrayObject(array(), ArrayObject::ARRAY_AS_PROPS);
        $this->stack = new SplStack;
        $this->stack[] = trim($fileName, \DIRECTORY_SEPARATOR);
        $this->basePath = rtrim($basePath, \DIRECTORY_SEPARATOR);
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
        if ($this->basePath) {
            return $this->basePath . \DIRECTORY_SEPARATOR . $fileName;
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

    /**
     */
    function render($fp)
    {
        foreach ($this->storage as ${"\x00key"} => ${"\x00val"}) {
            $${"\x00key"} = ${"\x00val"};
        }

        ob_start(function($buffer) use (&$fp) {
            fwrite($fp, $buffer);
        }, 1024 * 1024);
        include (string)$this;
        ob_end_flush();

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
