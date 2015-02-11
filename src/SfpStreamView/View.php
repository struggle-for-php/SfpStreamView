<?php
/**
 * post of parts is borrowed from spindle-view
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
    protected
        /** @var ArrayObject */ $_storage
    ,   /** @var string */ $_basePath
    ,   /** @var string */ $_fileName
    ,   /** @var string */ $_layoutFileName = ''
    ;

    protected $stack;

    /**
     * @param string $fileName 描画したいテンプレートのファイル名を指定します
     * @param string $basePath テンプレートの探索基準パスです。相対パスも指定できます。指定しなければinclude_pathから探索します。
     * @param ArrayObject $arr
     */
    function __construct($fileName, $basePath = '', ArrayObject $arr = null)
    {
        $this->_storage = $arr ?: new ArrayObject(array(), ArrayObject::ARRAY_AS_PROPS);
        //$this->_fileName = trim($fileName, \DIRECTORY_SEPARATOR);
        $this->stack = new SplStack;
        $this->stack[] = trim($fileName, \DIRECTORY_SEPARATOR);
        $this->_basePath = rtrim($basePath, \DIRECTORY_SEPARATOR);
    }

    /**
     * @return \ArrayIterator
     */
    function getIterator()
    {
        return $this->_storage->getIterator();
    }

    /**
     * @param string|int $name
     * @return mixed
     */
    function __get($name)
    {
        return $this->_storage[$name];
    }

    /**
     * @param string|int $name
     * @param mixed $value
     */
    function __set($name, $value)
    {
        $this->_storage[$name] = $value;
    }

    /**
     * @param string|int $name
     * @return bool
     */
    function __isset($name)
    {
        return isset($this->_storage[$name]);
    }

    /**
     * 描画するスクリプトファイルのパスを返します。
     * @return string
     */
    function __toString()
    {
        $fileName = $this->stack->pop();
        if ($this->_basePath) {
            return $this->_basePath . \DIRECTORY_SEPARATOR . $fileName;
        } else {
            return (string)$fileName;
        }
    }

    /**
     * セットされたview 変数を配列化して返します
     * @return array
     */
    function toArray()
    {
        return (array)$this->_storage;
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
            $this->_storage[$key] = $value;
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
        $s = $this->_storage;
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
     * テンプレートファイルを描画してstream にwrite
     * @return StreamableInterface
     */
    function render($fp)
    {

        foreach ($this->_storage as ${"\x00key"} => ${"\x00val"}) {
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
     * 親となるレイアウトテンプレートのファイル名を指定します。
     * レイアウトは__construct()で指定した基準パスと同じパスから探索します。
     * @param string $layoutFileName
     */
    function stackLayout($layoutFileName)
    {
        //$this->_layoutFileName = $layoutFileName;
        $this->stack[] = $layoutFileName;
    }

}
