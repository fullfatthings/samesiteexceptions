<?php

/**
 * @file SameSiteExceptionTest
 *
 * Tests for the same site exception class.
 *
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
     * Check exception thrown if invalid same site value passed.
     * @covers ::getSafeString
     */
    public function testGetSafeStringArgumentException()
    {
        $this->expectException("\UnexpectedValueException");
        SameSiteException::getSafeString('Foo');
    }

    /**
     * Check error thrown if no browser string available and errors not ignored.
     * @covers ::getSafeString
     */
    public function testGetSafeStringBrowserStringException()
    {
        $this->expectException('\Exception');
        SameSiteException::getSafeString('Lax', null, false);
    }

    /**
     * Check no error thrown in cli mode if no browser string and errors ignored.
     * @covers ::getSafeString
     */
    public function testGetSafeStringBrowserStringNoException()
    {
        $this->assertEquals('Lax', SameSiteException::getSafeString('Lax', null, true));
    }

    /**
     * Check server variable is picked up.
     */
    public function testGetSafeStringServerVar()
    {
        $_SERVER['HTTP_USER_AGENT'] = 'unknown';
        // No exception should be shown.
        $this->assertEquals('Lax', SameSiteException::getSafeString('Lax'));
    }

    /**
     * @covers ::isIosVersion()
     *
    * @dataProvider isIosVersionProvider
     *
     * @param string $user_agent
     *   User agent string.
     * @param integer $version
     *   Version to match for.
     * @param boolean $match
     *   Should match be true or false.
     *
     */
    public function testIsIosVersion($user_agent, $version, $match)
    {
        $this->assertEquals($match, SameSiteException::isIosVersion($version, $user_agent));
    }

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
     * @param string $user_agent
     *   User agent string,
     * @param int $major
     *   Major version to check for.
     * @param int $minor
     *   Minor version to check for.
     * @param bool $match
     *   Should a match be found.
     */
    public function testIsMacosxVersion($user_agent, $major, $minor, $match)
    {
        $this->assertEquals($match, SameSiteException::isMacosxVersion($major, $minor, $user_agent));
    }

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
     * @param string $user_agent
     *   User agent string
     * @param bool $match
     *   Should the string match.
     */
    public function testIsSafari($user_agent, $match)
    {
        $this->assertEquals($match, SameSiteException::isSafari($user_agent));
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
     * @param string $user_agent
     *   User agent string
     * @param bool $match
     *   Should the string match.
     */
    public function testIsChromiumBased($user_agent, $match)
    {
        $this->assertEquals($match, SameSiteException::isChromiumBased($user_agent));
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
     * @depends testIsChromiumBased
     * @depends testIsSafari
     * @depends testIsIosVersion
     *
     * @param string $user_agent
     *   User agent string.
     * @param bool $match
     *   Does this exhibit the webkit same site bug.
     */
    public function testHasWebKitSameSiteBug($user_agent, $match)
    {
        $this->assertEquals($match, SameSiteException::hasWebKitSameSiteBug($user_agent));
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

    /**
     * @covers ::isUCBrowser
     * @dataProvider isUCBrowserProvider
     *
     * @param string $user_agent
     *   The user agent string.
     * @param bool $match
     *   Should the string be UC Browser.
     */
    public function testIsUCBrowser($user_agent, $match)
    {
        $this->assertEquals($match, SameSiteException::isUCBrowser($user_agent));
    }

    public static function isUCBrowserProvider()
    {
        return [
          'is uc browser' => ['Mozilla/5.0 (Linux; U; Android 6.0.1; zh-CN; F5121 Build/34.0.A.1.247) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/40.0.2214.89 UCBrowser/11.5.1.944 Mobile Safari/537.36', true],
          'is firefox' => ['Mozilla/5.0 (Macintosh; Intel Mac OS X 10.10; rv:57.0) Gecko/20100101 Firefox/57.0', false]
        ];
    }

    /**
     * @covers ::isUcBrowserVersionAtLeast
     * @dataProvider isUcBrowserVersionAtLeastProvider
     *
     * @param string $user_agent
     *   User agent string
     * @param int $major
     *   Major version number
     * @param int $minor
     *   Minor version number
     * @param int $build
     *   Build number
     * @param bool $match
     *   Should the version match.
     */
    public function testIsUcBrowserVersionAtLeast($user_agent, $major, $minor, $build, $match)
    {
        $this->assertEquals($match, SameSiteException::isUcBrowserVersionAtLeast($major, $minor, $build, $user_agent));
    }

    public static function isUcBrowserVersionAtLeastProvider()
    {
        return [
          '11.5.1 check build' => ['Mozilla/5.0 (Linux; U; Android 6.0.1; zh-CN; F5121 Build/34.0.A.1.247) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/40.0.2214.89 UCBrowser/11.5.1.944 Mobile Safari/537.36', 11, 5, 0 , true],
          '11.5.1 check minor' => ['Mozilla/5.0 (Linux; U; Android 6.0.1; zh-CN; F5121 Build/34.0.A.1.247) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/40.0.2214.89 UCBrowser/11.5.1.944 Mobile Safari/537.36', 11, 4, 1 , true],
          '11.5.1 check major' => ['Mozilla/5.0 (Linux; U; Android 6.0.1; zh-CN; F5121 Build/34.0.A.1.247) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/40.0.2214.89 UCBrowser/11.5.1.944 Mobile Safari/537.36', 10, 5, 1 , true],
          '11.5.1 check greater build' => ['Mozilla/5.0 (Linux; U; Android 6.0.1; zh-CN; F5121 Build/34.0.A.1.247) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/40.0.2214.89 UCBrowser/11.5.1.944 Mobile Safari/537.36', 11, 5, 1 , true],
          '11.5.1 check fail build' => ['Mozilla/5.0 (Linux; U; Android 6.0.1; zh-CN; F5121 Build/34.0.A.1.247) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/40.0.2214.89 UCBrowser/11.5.1.944 Mobile Safari/537.36', 11, 5, 3 , false],
          '11.5.1 check fail minor' => ['Mozilla/5.0 (Linux; U; Android 6.0.1; zh-CN; F5121 Build/34.0.A.1.247) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/40.0.2214.89 UCBrowser/11.5.1.944 Mobile Safari/537.36', 11, 6, 1 , false],
          '11.5.1 check fail major' => ['Mozilla/5.0 (Linux; U; Android 6.0.1; zh-CN; F5121 Build/34.0.A.1.247) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/40.0.2214.89 UCBrowser/11.5.1.944 Mobile Safari/537.36', 12, 5, 1 , false],
          'firefox' => ['Mozilla/5.0 (Macintosh; Intel Mac OS X 10.10; rv:57.0) Gecko/20100101 Firefox/57.0', 12, 5, 1 , false],
        ];
    }

    /**
     * @covers ::isChromiumVersionAtLeast
     * @dataProvider isChromiumVersionAtLeastProvider
     *
     * @param string $user_agent
     *   User agent string.
     * @param int $major
     *   Major version
     * @param bool $match
     *   Should the versions match
     */
    public function testIsChromiumVersionAtLeast($user_agent, $major, $match)
    {
        $this->assertEquals($match, SameSiteException::isChromiumVersionAtLeast($major, $user_agent));
    }

    public static function isChromiumVersionAtLeastProvider()
    {
        return [
          'Chrome 70 check at least 69' => ["Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/70.0.3163.100 Safari/537.36", 69, true],
          'Chrome 70 check at least 70' => ["Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/70.0.3163.100 Safari/537.36", 70, true],
          'Chrome 70 check at least 71' => ["Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/70.0.3163.100 Safari/537.36", 71, false],
          'firefox' => ['Mozilla/5.0 (Macintosh; Intel Mac OS X 10.10; rv:57.0) Gecko/20100101 Firefox/57.0', 69 , false],
        ];
    }

    /**
     * @covers ::dropsUnrecognizedSameSiteCookies
     * @dataProvider dropsUnrecognizedSameSiteCookiesProvider
     * @depends testIsChromiumVersionAtLeast
     * @depends testIsChromiumBased
     * @depends testIsUCBrowser
     * @depends testIsUcBrowserVersionAtLeast
     *
     * @param string $user_agent
     *   User agent string
     * @param bool  $match
     *   True is the user agent is known to drop unrecognised samesite cookies.
     */
    public function testDropsUnrecognizedSameSiteCookies($user_agent, $match)
    {
        $this->assertEquals($match, SameSiteException::dropsUnrecognizedSameSiteCookies($user_agent));
    }

    public static function dropsUnrecognizedSameSiteCookiesProvider()
    {
        return [
        'Chrome 50' => ["Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/50.0.2661.102 Safari/537.36", false],
        'Chrome 60' => ["Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/60.0.3112.78 Safari/537.36,", true],
        'UC browser 12.13.5' => ["Mozilla/5.0 (Linux; U; Android 10; en-US; GM1911 Build/QKQ1.190716.003) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/57.0.2987.108 UCBrowser/12.13.5.1209 Mobile Safari/537.36", false],
        'Uc Browser 11.5.1' => ['Mozilla/5.0 (Linux; U; Android 6.0.1; zh-CN; F5121 Build/34.0.A.1.247) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/40.0.2214.89 UCBrowser/11.5.1.944 Mobile Safari/537.36', true],
        'Chrome 70' => ["Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/70.0.3163.100 Safari/537.36", false],
        'firefox' => ['Mozilla/5.0 (Macintosh; Intel Mac OS X 10.10; rv:57.0) Gecko/20100101 Firefox/57.0', false],
            ];
    }

    /**
     * @covers ::isSameSiteNoneIncompatible
     * @dataProvider isSameSiteNoneIncompatibleProvider
     * @depends testHasWebKitSameSiteBug
     * @depends testDropsUnrecognizedSameSiteCookies
     *
     * @param string $user_agent
     *   The user agent.
     * @param bool $expected_compatibility
     *   Expected return value.
     */
    public function testIsSameSiteNoneIncompatible($user_agent, $expected_compatibility)
    {
        $this->assertEquals($expected_compatibility, SameSiteException::isSameSiteNoneIncompatible($user_agent));
    }

    public static function isSameSiteNoneIncompatibleProvider()
    {
        return [
          'Chrome 70' => ["Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/70.0.3163.100 Safari/537.36", false],
          'Safari OSX 10.14' => ["Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_5) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/12.1.1 Safari/605.1.15", true],
          'IOS 12' => ["Mozilla/5.0 (iPhone; CPU iPhone OS 12_0 like Mac OS X) AppleWebKit/ 604.1.21 (KHTML, like Gecko) Version/ 12.0 Mobile/17A6278a Safari/602.1.26", true]
        ];
    }

    /**
     * @covers ::getSafeString
     * @dataProvider getSafeStringProvider
     * @depends testIsSameSiteNoneIncompatible
     *
     * @param string $safe_string
     *   The safe string
     * @param string $user_agent
     *   The user agent
     * @param string $expected
     *   The expected safe string
     * @throws \Exception
     */
    public function testGetSafeString($safe_string, $user_agent, $expected)
    {
        $this->assertEquals($expected, SameSiteException::getSafeString($safe_string, $user_agent));
    }

    public static function getSafeStringProvider()
    {
        return [
          'chrome 70 Lax' => ["Lax", "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/70.0.3163.100 Safari/537.36", "Lax"],
          'chrome 70 None' => ["None", "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/70.0.3163.100 Safari/537.36", "None"],
          'chrome 70 Strict' => ["Strict", "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/70.0.3163.100 Safari/537.36", "Strict"],
          'IOS 12 Lax' => ["Lax", "Mozilla/5.0 (iPhone; CPU iPhone OS 12_0 like Mac OS X) AppleWebKit/ 604.1.21 (KHTML, like Gecko) Version/ 12.0 Mobile/17A6278a Safari/602.1.26", "Lax"],
          'IOS 12 Strict' => ["Strict", "Mozilla/5.0 (iPhone; CPU iPhone OS 12_0 like Mac OS X) AppleWebKit/ 604.1.21 (KHTML, like Gecko) Version/ 12.0 Mobile/17A6278a Safari/602.1.26", "Strict"],
          'IOS 12 None' => ["None", "Mozilla/5.0 (iPhone; CPU iPhone OS 12_0 like Mac OS X) AppleWebKit/ 604.1.21 (KHTML, like Gecko) Version/ 12.0 Mobile/17A6278a Safari/602.1.26", null],
          'Safari OSX 10.14 Lax' => ["Lax", "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_5) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/12.1.1 Safari/605.1.15", "Lax"],
          'Safari OSX 10.14 Strict' => ["Strict", "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_5) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/12.1.1 Safari/605.1.15", "Strict"],
          'Safari OSX 10.14 None' => ["None", "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_5) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/12.1.1 Safari/605.1.15", null]
        ];
    }
}
