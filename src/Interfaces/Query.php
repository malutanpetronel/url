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
namespace League\Url\Interfaces;

use Countable;
use IteratorAggregate;
use JsonSerializable;

/**
 * An interface for URL Query component
 *
 * @package  League.url
 * @since  4.0.0
 */
interface Query extends Component, Countable, IteratorAggregate, JsonSerializable
{
    /**
     * Return an array representation of the Query
     *
     * @return array
     */
    public function toArray();

    /**
     * Return the query keys. If a value is specified
     * only the key for that value are returned
     *
     * @param mixed $data
     *
     * @return array
     */
    public function offsets($data = null);

    /**
     * Tell whether the given offset exists in the Query object
     *
     * @param string $offset
     *
     * @return bool
     */
    public function hasOffset($offset);

    /**
     * Return a value from the Query object
     *
     * @param string $offset  the parameter name
     * @param mixed  $default if no key is found the default value to return
     *
     * @return mixed
     */
    public function getParameter($offset, $default = null);

    /**
     * Merge new data to the current Query object
     * and return a new modified Query object
     *
     * @param Query $query
     *
     * @return static
     */
    public function merge(Query $query);

    /**
     * Remove the data corresponding to the offset
     * from the object and return a new instance with
     * the modified data
     *
     * @param array $offsets
     *
     * @return static
     */
    public function without(array $offsets);
}