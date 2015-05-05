<?php

namespace League\Url\Test\Components;

use ArrayIterator;
use League\Url\Host;
use PHPUnit_Framework_TestCase;

/**
 * @group segment
 */
class HostTest extends PHPUnit_Framework_TestCase
{
    /**
     * Test valid Host
     * @param string $host
     * @param bool $isIp
     * @param bool $isIpv4
     * @param bool $isIpv6
     * @param string $str
     * @param string $uri
     * @dataProvider validHostProvider
     */
    public function testValidHost($host, $isIp, $isIpv4, $isIpv6, $str, $uri)
    {
        $host = new Host($host);
        $this->assertSame($isIp, $host->isIp());
        $this->assertSame($isIpv4, $host->isIpv4());
        $this->assertSame($isIpv6, $host->isIpv6());
        $this->assertSame($str, $host->get());
        $this->assertSame($uri, $host->getUriComponent());
    }

    public function validHostProvider()
    {
        return [
            'ipv4' => ['127.0.0.1', true, true, false, '127.0.0.1', '127.0.0.1'],
            'naked ipv6' => ['FE80:0000:0000:0000:0202:B3FF:FE1E:8329', true, false, true, 'FE80:0000:0000:0000:0202:B3FF:FE1E:8329', '[FE80:0000:0000:0000:0202:B3FF:FE1E:8329]'],
            'ipv6' => ['[::1]', true, false, true, '::1', '[::1]'],
            'string' => ['Master.EXAMPLE.cOm', false, false, false, 'master.example.com', 'master.example.com'],
            'null' => [null, false, false, false, null, ''],
            'dot ending' => ['example.com.', false, false, false, 'example.com.', 'example.com.'],
        ];
    }

    /**
     * @param              string $invalid
     * @dataProvider       invalidHostProvider
     * @expectedException \InvalidArgumentException
     */
    public function testInvalidHost($invalid)
    {
        new Host($invalid);
    }

    public function invalidHostProvider()
    {
        return [
            ['.example.com'],
            ['.......'],
            ['tot.    .coucou.com'],
            ['re view'],
            ['_bad.host.com'],
            [implode('', array_fill(0, 23, 'banana')).'secure.example.com'],
            [implode('.', array_fill(0, 128, 'a'))],
            [implode('.', array_fill(0, 23, 'banana-slip'))],
            ['[127.0.0.1]'],
            ['toto.127.0.0.1'],
            ['98.3.2'],
            ['[[::1]]'],
            ['23.42c.two'],
        ];
    }

    /**
     * @param $raw
     * @param $expected
     * @dataProvider isAbsoluteProvider
     */
    public function testIsAbsolute($raw, $expected)
    {
        $this->assertSame($expected, (new Host($raw))->isAbsolute());
    }

    public function isAbsoluteProvider()
    {
        return [
            ['127.0.0.1', false],
            ['example.com.', true],
            ['example.com', false],
        ];
    }

    /**
     * Test Punycode support
     *
     * @param string $unicode Unicode Hostname
     * @param string $ascii   Ascii Hostname
     * @dataProvider hostnamesProvider
     */
    public function testValidUnicodeHost($unicode, $ascii)
    {
        $host = new Host($unicode);
        $this->assertSame($ascii, $host->toAscii());
        $this->assertSame($unicode, $host->toUnicode());
        $this->assertSame($unicode, $host->__toString());
    }

    public function hostnamesProvider()
    {
        // http://en.wikipedia.org/wiki/.test_(international_domain_name)#Test_TLDs
        return [
            ['مثال.إختبار', 'xn--mgbh0fb.xn--kgbechtv'],
            ['مثال.آزمایشی', 'xn--mgbh0fb.xn--hgbk6aj7f53bba'],
            ['例子.测试', 'xn--fsqu00a.xn--0zwm56d'],
            ['例子.測試', 'xn--fsqu00a.xn--g6w251d'],
            ['пример.испытание', 'xn--e1afmkfd.xn--80akhbyknj4f'],
            ['उदाहरण.परीक्षा', 'xn--p1b6ci4b4b3a.xn--11b5bs3a9aj6g'],
            ['παράδειγμα.δοκιμή', 'xn--hxajbheg2az3al.xn--jxalpdlp'],
            ['실례.테스트', 'xn--9n2bp8q.xn--9t4b11yi5a'],
            ['בײַשפּיל.טעסט', 'xn--fdbk5d8ap9b8a8d.xn--deba0ad'],
            ['例え.テスト', 'xn--r8jz45g.xn--zckzah'],
            ['உதாரணம்.பரிட்சை', 'xn--zkc6cc5bi7f6e.xn--hlcj6aya9esc7a'],
            ['derhausüberwacher.de', 'xn--derhausberwacher-pzb.de'],
            ['renangonçalves.com', 'xn--renangonalves-pgb.com'],
            ['рф.ru', 'xn--p1ai.ru'],
            ['δοκιμή.gr', 'xn--jxalpdlp.gr'],
            ['ফাহাদ্১৯.বাংলা', 'xn--65bj6btb5gwimc.xn--54b7fta0cc'],
            ['𐌀𐌖𐌋𐌄𐌑𐌉·𐌌𐌄𐌕𐌄𐌋𐌉𐌑.gr', 'xn--uba5533kmaba1adkfh6ch2cg.gr'],
            ['guangdong.广东', 'guangdong.xn--xhq521b'],
            ['gwóźdź.pl', 'xn--gwd-hna98db.pl'],
        ];
    }

    /**
     * Test Countable
     *
     * @param $host
     * @param $nblabels
     * @param $array
     * @dataProvider countableProvider
     */
    public function testCountable($host, $nblabels, $array)
    {
        $obj = new Host($host);
        $this->assertCount($nblabels, $obj);
        $this->assertSame($array, $obj->toArray());
    }

    public function countableProvider()
    {
        return [
            'ip' => ['127.0.0.1', 1, ['127.0.0.1']],
            'string' => ['secure.example.com', 3, ['secure', 'example', 'com']],
        ];
    }

    /**
     * @param $input
     * @param $is_absolute
     * @param $expected
     * @dataProvider createFromArrayValid
     */
    public function testCreateFromArray($input, $is_absolute, $expected)
    {
        $this->assertSame($expected, Host::createFromArray($input, $is_absolute)->__toString());
    }

    public function createFromArrayValid()
    {
        return [
            'array' => [['www', 'example', 'com'], false, 'www.example.com',],
            'iterator' => [new ArrayIterator(['www', 'example', 'com']), false, 'www.example.com',],
            'host object' => [new Host('::1'), false, '[::1]'],
            'ip 1' => [[127, 0, 0, 1], false, '127.0.0.1'],
            'ip 2' => [['127.0', '0.1'], false, '127.0.0.1'],
            'ip 3' => [['127.0.0.1'], false, '127.0.0.1'],
            'FQDN' => [['www', 'example', 'com'], true, 'www.example.com.'],
        ];
    }

    /**
     * @param $input
     * @param $is_absolute
     * @dataProvider createFromArrayInvalid
     * @expectedException \InvalidArgumentException
     */
    public function testCreateFromArrayFailed($input, $is_absolute)
    {
        Host::createFromArray($input, $is_absolute);
    }

    public function createFromArrayInvalid()
    {
        return [
            'string' => ['www.example.com', false],
            'bool' => [true, false],
            'integer' => [1, false],
            'object' => [new \StdClass(), false],
            'ip FQDN' => [['127.0.0.1'], true],
            'ipv6 FQDN' => [['::1'], true],
        ];
    }

    public function testGetLabel()
    {
        $host = new Host('master.example.com');
        $this->assertSame('master', $host->getLabel(0));
        $this->assertNull($host->getLabel(23));
        $this->assertSame('toto', $host->getLabel(23, 'toto'));
    }

    public function testoffsets()
    {
        $host = new Host('master.example.com');
        $this->assertSame([0, 1, 2], $host->offsets());
        $this->assertSame([1], $host->offsets('example'));
    }

    /**
     * @param string $host1
     * @param string $host2
     * @param bool $bool
     * @dataProvider sameValueAsProvider
     */
    public function testSameValueAs($host1, $host2, $bool)
    {
        $this->assertSame($bool, (new Host($host1))->sameValueAs(new Host($host2)));
    }

    public function sameValueAsProvider()
    {
        return [
            ['master.example.com', 'MaStEr.ExAMple.CoM', true],
            ['::1', '::1', true],
            ['toto.com', 'barbaz.be', false],
        ];
    }

    /**
     * @param string $host
     * @param $without
     * @param $res
     * @dataProvider withoutProvider
     */
    public function testWithout($host, $without, $res)
    {
        $this->assertSame($res, (new Host($host))->without($without)->__toString());
    }

    public function withoutProvider()
    {
        return [
            ['secure.example.com', [0], 'example.com'],
            ['127.0.0.1', [0, 1] , ''],
            ['127.0.0.1', [0], ''],
        ];
    }

    public function testprepend()
    {
        $host    = new Host('secure.example.com');
        $newHost = $host->prepend(new Host('master'));
        $this->assertSame('master.secure.example.com', $newHost->get());
    }

    /**
     * @expectedException LogicException
     */
    public function testprependIpFailed()
    {
        (new Host('127.0.0.1'))->prepend(new Host('foo'));
    }

    public function testappend()
    {
        $host    = new Host('secure.example.com');
        $newHost = $host->append(new Host('shop'));
        $this->assertSame('secure.example.com.shop', $newHost->get());
    }

    /**
     * @expectedException LogicException
     */
    public function testappendIpFailed()
    {
        (new Host('127.0.0.1'))->append(new Host('foo'));
    }

    /**
     * @param $raw
     * @param $input
     * @param $offset
     * @param $expected
     * @dataProvider replaceValid
     */
    public function testreplace($raw, $input, $offset, $expected)
    {
        $host = new Host($raw);
        $newHost = $host->replace($input, $offset);
        $this->assertSame($expected, $newHost->get());
    }

    public function replaceValid()
    {
        return [
            ['master.example.com', new Host('shop'), 0, 'shop.example.com'],
            ['', new Host('::1'), 0, '::1'],
            ['toto', new Host('::1'), 23, 'toto'],
        ];
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testreplaceIpMustFailed()
    {
        $host = new Host('secure.example.com');
        $host->replace(new Host('127.0.0.1'), 2);
    }
}