<?php
/**
* This file is part of the League.url library
*
* @license http://opensource.org/licenses/MIT
* @link https://github.com/thephpleague/url/
* @version 4.0.0
* @package League.url
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/
namespace League\Url;

use InvalidArgumentException;
use League\Url\Interfaces;
use Traversable;

/**
 * An abstract class to ease component creation
 *
 * @package  League.url
 * @since  1.0.0
 */
class Query implements Interfaces\Query
{
    /**
     * Trait for Collection type Component
     */
    use Util\CollectionComponent;

    /**
     * a new instance
     *
     * @param string $data
     */
    public function __construct($data = null)
    {
        if (! is_null($data)) {
            $this->data = $this->validate($data);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function withValue($value)
    {
        return new static($value);
    }

    /**
     * return a new Query instance from an Array or a traversable object
     *
     * @param  \Traversable|array $data
     *
     * @throws \InvalidArgumentException If $data is invalid
     *
     * @return static
     */
    public static function createFromArray($data)
    {
        return new static(http_build_query(static::validateIterator($data), '', '&', PHP_QUERY_RFC3986));
    }

    /**
     * sanitize the submitted data
     *
     * @param string $str
     *
     * @return array
     */
    protected function validate($str)
    {
        if (is_bool($str)) {
            throw new InvalidArgumentException('Data passed must be a valid string; received a boolean');
        }

        $str = $this->validateString($str);
        if (empty($str)) {
            return [];
        }

        $str = preg_replace_callback('/(?:^|(?<=&))[^=|&[]+/', function ($match) {
            return bin2hex(urldecode($match[0]));
        }, $str);
        parse_str($str, $arr);

        $arr = array_combine(array_map('hex2bin', array_keys($arr)), $arr);

        return array_filter($arr, function ($value) {
            return ! is_null($value);
        });
    }

    /**
     * {@inheritdoc}
     */
    public function get()
    {
        if (empty($this->data)) {
            return null;
        }

        return preg_replace(
            [',=&,', ',=$,'],
            ['&', ''],
            http_build_query($this->data, '', '&', PHP_QUERY_RFC3986)
        );
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        return (string) $this->get();
    }

    /**
     * {@inheritdoc}
     */
    public function getUriComponent()
    {
        $res = $this->__toString();
        if (empty($res)) {
            return $res;
        }

        return '?'.$res;
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize()
    {
        return $this->data;
    }

    /**
     * {@inheritdoc}
     */
    public function offsets($parameter = null)
    {
        if (is_null($parameter)) {
            return array_keys($this->data);
        }

        return array_keys($this->data, $parameter, true);
    }

    /**
     * {@inheritdoc}
     */
    public function getParameter($offset, $default = null)
    {
        if (isset($this->data[$offset])) {
            return $this->data[$offset];
        }

        return $default;
    }

    /**
     * {@inheritdoc}
     */
    public function merge(Interfaces\Query $query)
    {
        return static::createFromArray(array_merge($this->data, $query->toArray()));
    }
}