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

/**
* A class to manipulate an URL output
*
* @package League.url
* @since 4.0.0
*/
class Formatter
{
    const HOST_ASCII = 1;

    const HOST_UNICODE = 2;

    /**
     * host encoding property
     *
     * @var int
     */
    protected $hostEncoding = self::HOST_UNICODE;

    /**
     * query encoding property
     *
     * @var int
     */
    protected $queryEncoding = PHP_QUERY_RFC3986;

    /**
     * query separator property
     *
     * @var string
     */
    protected $querySeparator = '&';

    /**
     * Host encoding setter
     *
     * @param int $encode a predefined constant value
     */
    public function setHostEncoding($encode)
    {
        if (! in_array($encode, [self::HOST_UNICODE, self::HOST_ASCII])) {
            throw new InvalidArgumentException('Unknown Host encoding rule');
        }
        $this->hostEncoding = $encode;
    }

    /**
     * Host encoding getter
     *
     * @return int
     */
    public function getHostEncoding()
    {
        return $this->hostEncoding;
    }

    /**
     * Query encoding setter
     *
     * @param int $encode  a predefined constant value
     */
    public function setQueryEncoding($encode)
    {
        if (! in_array($encode, [PHP_QUERY_RFC3986, PHP_QUERY_RFC1738])) {
            throw new InvalidArgumentException('Unknown Query encoding rule');
        }
        $this->queryEncoding = $encode;
    }

    /**
     * Query encoding getter
     *
     * @return int
     */
    public function getQueryEncoding()
    {
        return $this->queryEncoding;
    }

    /**
     * Query separator setter
     *
     * @param string $separator
     */
    public function setQuerySeparator($separator)
    {
        $separator = filter_var($separator, FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW);

        $this->querySeparator = trim($separator);
    }

    /**
     * Query separator getter
     *
     * @return string
     */
    public function getQuerySeparator()
    {
        return $this->querySeparator;
    }

    /**
     * Format an object according to the formatter properties
     *
     * @param Interfaces\Component|Interfaces\Url $input
     *
     * @throws \InvalidArgumentException if the given $input can not be formatted
     *
     * @return string
     */
    public function format($input)
    {
        if ($input instanceof Interfaces\Url) {
            return $this->formatUrl($input);
        }

        if ($input instanceof Interfaces\Component) {
            return $this->formatComponent($input);
        }

        throw new InvalidArgumentException("The submitted value can not be formatted");
    }

    /**
     * Format a League\Url\Interfaces\Component
     *
     * @param Interfaces\Component $component
     *
     * @return string
     */
    protected function formatComponent(Interfaces\Component $component)
    {
        if ($component instanceof Interfaces\Query) {
            return $this->formatQuery($component);
        }

        if ($component instanceof Interfaces\Host) {
            return $this->formatHost($component);
        }

        return $component->__toString();
    }

    /**
     * Format a League\Url\Interfaces\Query
     *
     * @param Interfaces\Query $query
     *
     * @return string
     */
    protected function formatQuery(Interfaces\Query $query)
    {
        $sep = preg_quote($this->querySeparator, ',');

        return preg_replace(
            [",=$sep,", ",=$,"],
            [$this->querySeparator, ''],
            http_build_query(
                $query->toArray(),
                null,
                $this->querySeparator,
                $this->queryEncoding
            )
        );
    }

    /**
     * Format a League\Url\Interfaces\Host
     *
     * @param Interfaces\Host $host
     *
     * @return string
     */
    protected function formatHost(Interfaces\Host $host)
    {
        if (self::HOST_ASCII == $this->hostEncoding) {
            return $host->toAscii();
        }

        return $host->toUnicode();
    }

    /**
     * Format a League\Url\Interfaces\Url
     *
     * @param Interfaces\Url $url
     *
     * @return string
     */
    protected function formatUrl(Interfaces\Url $url)
    {
        $host  = $url->getHost();
        $query = $url->getQuery();
        $str   = $url->getScheme()->getUriComponent();
        if (count($host)) {
            $userinfo = $url->getUserInfo();
            if (! empty($userinfo)) {
                $userinfo .= '@';
            }
            $str .= '//'.$userinfo.$this->formatHost($host);
            if (! $url->hasStandardPort()) {
                $str .= $url->getPort()->getUriComponent();
            }
        }

        $str .= $url->getPath()->getUriComponent();
        if (count($query)) {
            $str .= '?'.$this->formatQuery($query);
        }

        return $str.$url->getFragment()->getUriComponent();
    }
}