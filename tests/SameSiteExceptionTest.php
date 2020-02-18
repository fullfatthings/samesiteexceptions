<?php
/**
 * @file
 */

namespace FullFatThings\SameSiteException\Tests;

use PHPUnit\Framework\TestCase;
use FullFatThings\SameSiteException\SameSiteException;

/**
 * Class SameSiteExceptionTest
 * @package FullFatThings\SameSiteException
 * @coversDefaultClass \FullFatThings\SameSiteException\SameSiteException
 */
class SameSiteExceptionTest extends TestCase
{

    /**
     * @covers ::isIosVersion()
     *
    * @dataProvider isIosVersionProvider
     *
     * @param string $string
     *   User agent string.
     * @param int version
     *   Version to match for.
     * @param boolean $match
     *   Should match be true or false.
     *
     */
    public function testIsIosVersion($string, $version, $match)
    {
        $this->assertEquals($match, SameSiteException::isIosVersion($version, $string));
    }

    /**
     * Data provider for testIsIosVersion()
     *
     * @return array
     */
    public static function isIosVersionProvider()
    {
        return [
          "ios 12 looking for 12" => ["Mozilla/5.0 (iPhone; CPU iPhone OS 12_0 like Mac OS X) AppleWebKit/ 604.1.21 (KHTML, like Gecko) Version/ 12.0 Mobile/17A6278a Safari/602.1.26" ,12, true],
          "ios 12 looking for 11" => ["Mozilla/5.0 (iPhone; CPU iPhone OS 12_0 like Mac OS X) AppleWebKit/ 604.1.21 (KHTML, like Gecko) Version/ 12.0 Mobile/17A6278a Safari/602.1.26", 11, false],
          "chrome looking for ios 12" => ["Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/60.0.3112.78 Safari/537.36,", 12, false],
          "firefox on ios 12 looking for 12" => ["Mozilla/5.0 (iPad; CPU OS 12_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) FxiOS/13.2b11866 Mobile/16A366 Safari/605.1.15", 12, true],
          "ios13 looking for 12" => ["Mozilla/5.0 (iPhone; CPU iPhone OS 13_1_3 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Mobile/15E148 [FBAN/FBIOS;FBDV/iPhone10,5;FBMD/iPhone;FBSN/iOS;FBSV/13.1.3;FBSS/3;FBID/phone;FBLC/en_US;FBOP/5;FBCR/T-Mobile]", 12, false],
        ];
    }

    /**
     * @covers ::isMacosxVersion()
     *
     * @dataProvider  isMacosxVersionProvider
     *
     * @param string $string
     *   User agent string,
     * @param int $major
     *   Major version to check for.
     * @param int $minor
     *   Minor version to check for.
     * @param boolean $match
     *   Should a match be found.
     */
    public function testIsMacosxVersion($string, $major, $minor, $match)
    {
        $this->assertEquals($match, SameSiteException::isMacosxVersion($major, $minor, $string));
    }

    /**
     * Data provider for testIsMacosxVersion.
     *
     * @return array
     */
    public static function isMacosxVersionProvider()
    {
        return [
          'OSX 10 11 negative test' => ["Mozilla/5.0 (Macintosh; Intel Mac OS X 10_11_6) AppleWebKit/601.7.7 (KHTML, like Gecko) Version/9.1.2 Safari/601.7.7", 10,13, false],
          'OSX 10 11 positive test' => ["Mozilla/5.0 (Macintosh; Intel Mac OS X 10_11_6) AppleWebKit/601.7.7 (KHTML, like Gecko) Version/9.1.2 Safari/601.7.7", 10,11, true],
          'OSX 10 13 negative test' => ["Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_3) AppleWebKit/604.5.6 (KHTML, like Gecko) Version/11.0.3 Safari/604.5.6", 10,10, false],
          'OSX 10 13 positive test' => ["Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_3) AppleWebKit/604.5.6 (KHTML, like Gecko) Version/11.0.3 Safari/604.5.6", 10,13, true],
          'OSX 10 14 positive test' => ["Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/70.0.3538.102 Safari/537.36", 10, 14, true],
        ];
    }

    /**
     * @covers ::isSafari
     *
     * @dataProvider isSafariProvider()
     *
     * @param string $string
     *   User agent string
     * @param boolean $match
     *   Should the string match.
     */
    public function testIsSafari($string, $match)
    {
        $this->assertEquals($match, SameSiteException::isSafari($string));
    }

    public static function isSafariProvider()
    {
        return [
          'firefox' => ["Mozilla/5.0 (Macintosh; Intel Mac OS X 10.10; rv:57.0) Gecko/20100101 Firefox/57.0", false],
          'Native safari' => ["Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_3) AppleWebKit/604.5.6 (KHTML, like Gecko) Version/11.0.3 Safari/604.5.6", true],
          'Chromium safari' => ["Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/70.0.3538.102 Safari/537.36", false]
        ];
    }


    /**
     * @covers ::isChromiumBased
     *
     * @dataProvider isChromiumBasedProvider()
     *
     * @param string $string
     *   User agent string
     * @param boolean $match
     *   Should the string match.
     */
    public function testIsChromiumBased($string, $match)
    {
        $this->assertEquals($match, SameSiteException::isChromiumBased($string));
    }

    public static function isChromiumBasedProvider()
    {
        return [
          'firefox' => ["Mozilla/5.0 (Macintosh; Intel Mac OS X 10.10; rv:57.0) Gecko/20100101 Firefox/57.0", false],
          'Native safari' => ["Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_3) AppleWebKit/604.5.6 (KHTML, like Gecko) Version/11.0.3 Safari/604.5.6", false],
          'Chromium safari' => ["Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/70.0.3538.102 Safari/537.36", true]
        ];
    }


    /**
     * @covers ::hasWebKitSameSiteBug
     * @dataProvider hasWebKitSameSiteBugProvider
     *
     * @param string $string
     *   User agent string.
     * @param boolean $match
     *   Does this exhibit the webkit same site bug.
     */
    public function testHasWebKitSameSiteBug($string, $match)
    {
        $this->assertEquals($match, SameSiteException::hasWebKitSameSiteBug($string));
    }

    public static function hasWebKitSameSiteBugProvider()
    {
        return [
          'firefox pre OSX 10.13' => ["Mozilla/5.0 (Macintosh; Intel Mac OS X 10.10; rv:57.0) Gecko/20100101 Firefox/57.0", false],
          'Chromium safari OSX 10.14' => ["Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/70.0.3538.102 Safari/537.36", false],
          'IOS 12' => ['Mozilla/5.0 (iPhone; CPU iPhone OS 12_0 like Mac OS X) AppleWebKit/ 604.1.21 (KHTML, like Gecko) Version/ 12.0 Mobile/17A6278a Safari/602.1.26', true],
          'Safari OSX 10.14' => ["Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_5) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/12.1.1 Safari/605.1.15", true]
        ];
    }


}
