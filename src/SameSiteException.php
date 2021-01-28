<?php

/**
 * @file SameSiteException
 *
 * Manage values for samesite cookies and incompatible clients.
 *
 * Taken from pseudocode here
 * @see https://www.chromium.org/updates/same-site/incompatible-clients
 */

namespace FullFatThings\SameSiteException;

use UnexpectedValueException;
use Exception;

class SameSiteException
{
    const SAFARI_REGEX = "/Version\/.* Safari\//";
    const CHROME_REGEX = "/Chrom(e|ium)/";
    const UCBROWSER_REGEX = "/UCBrowser\/(\d+)\.(\d+)\.(\d+)[\.\d]* /";
    const EMBEDDED_MAC_REGEX = "|Mozilla.*\(Macintosh;.*Mac OS X [_\d]+\).*AppleWebKit/[\.\d]+ \(KHTML, like Gecko\)$|";

    const IOS_VERSION_REGEX = "/\(iP.+; CPU .*OS (\d+)[_\d]*.*\) AppleWebKit\//";
    const OSX_VERSION_REGEX = "/\(Macintosh;.*Mac OS X (\d+)_(\d+)[_\d]*.*\) AppleWebKit\//";
    const CHROME_VERSION_REGEX = "/Chrom[^ \/]+\/(\d+)[\.\d]* /";

    public static $VALID_SAMESITE_VALUES = array('None', 'Lax', 'Strict');

    /**
     * Get a safe string to use in a same site cookie for the given browser.
     *
     * @param string $same_site
     *   The same site setting you wish to set.
     * @param string $user_agent_string
     *   The user agent string string for the request. if null this will try
     *   to take from the current request.
     * @param boolean $ignore_cli_errors
     *   Normally if php cli is used exceptions will not be thrown if no user agent.
     *   Set this to false and errors will be thrown.
     *
     *
     * @return string|null
     *   string for a safe string to use, null if you should not set SameSite at all.
     *
     * @throws \UnexpectedValueException
     *   If the value passed is not valid for a same site cookie.
     * @throws \Exception
     *   If no user agent can be found in the server structure.
     */
    public static function getSafeString($same_site, $user_agent_string = null, $ignore_cli_errors = true)
    {
        // Check for valid versions of same_site attribute
        if ($same_site != null && array_search($same_site, self::$VALID_SAMESITE_VALUES) === false) {
            throw new UnexpectedValueException("value passed is not valid for a samesite cookie");
        }

        // get user agent if not passed.
        if ($user_agent_string == null) {
            if (isset($_SERVER['HTTP_USER_AGENT']) && !empty($_SERVER['HTTP_USER_AGENT'])) {
                $user_agent_string = $_SERVER['HTTP_USER_AGENT'];
            } else {
                if (php_sapi_name() != 'cli' || (php_sapi_name() === 'cli' && !$ignore_cli_errors)) {
                    throw new Exception("No User agent can be found to test");
                } else {
                    return $same_site;
                }
            }
        }



        // Drop the samesite value if it is incompatible.
        if ($same_site == 'None') {
            if (self::isSameSiteNoneIncompatible($user_agent_string)) {
                return null;
            }
        }

        return $same_site;
    }

    /**
     * Is the user agent string known to be same site none incompatible.
     *
     * @param string $user_agent_string
     *   The user agent string to test.
     *
     * @return boolean
     *   True if the user agent is known not to handle same site none.
     */
    public static function isSameSiteNoneIncompatible($user_agent_string)
    {
        return  self::hasWebKitSameSiteBug($user_agent_string) || self::dropsUnrecognizedSameSiteCookies($user_agent_string);
    }

    /**
     * Detect if the user agent string is known to have the webkit same site bug.
     * @param string $user_agent_string
     *   User agent string to test.
     * @return bool
     *   True if the given user agent exhibits the webkit samesite bug.
     */
    public static function hasWebKitSameSiteBug($user_agent_string)
    {
        return (self::isIosVersion(12, $user_agent_string) ||
          (
            self::isMacosxVersion(10, 14, $user_agent_string) &&
            (self::isSafari($user_agent_string) || self::isMacEmbeddedBrowser($user_agent_string))
          )

        );
    }

    /**
     * Detect if the user agent string is a Mac embedded browser
     * @param string $user_agent_string
     *   User agent string to test.
     * @return bool
     *   True if the given user agent is Mac embedded browser.
     */
    public static function isMacEmbeddedBrowser($user_agent_string)
    {
        return (bool) preg_match(self::EMBEDDED_MAC_REGEX, $user_agent_string);
    }

    /**
     * Detect if the user agent string is known to drop unrecognized same site cookies.
     *
     * @param string $user_agent_string
     *   User agent string to test

     * @return bool
     *   True if the browser string is known to exhibit the bug.
     */
    public static function dropsUnrecognizedSameSiteCookies($user_agent_string)
    {
        if (self::isUcBrowser($user_agent_string)) {
            return !self::isUcBrowserVersionAtLeast(12, 13, 2, $user_agent_string);
        } elseif (self::isChromiumBased($user_agent_string)) {
                return self::isChromiumVersionAtLeast(51, $user_agent_string) &&
                  ! self::isChromiumVersionAtLeast(67, $user_agent_string);
        }
        return false;
    }

    /**
     * Does the user agent string match an Mac OSX version
     *
     * @param int $major
     *   Major version number.
     * @param int $minor
     *   Minor version number.
     * @param string $user_agent_string
     *   User agent string to test
     *
     * @return bool
     *   True if the user agent is OSX and the correct version.
     */
    public static function isMacosxVersion($major, $minor, $user_agent_string)
    {
        $matches = array();
        if (preg_match(self::OSX_VERSION_REGEX, $user_agent_string, $matches)) {
            if ($matches[1] == $major && $matches[2] == $minor) {
                return true;
            }
        }
        return false;
    }


    /**
     * Detect if the user agent string is a specific IOS Version
     * @param int $major
     *   The version number to check for.
     * @param string $user_agent_string
     *   The user agent string that is expected.
     * @return bool
     *   True if the user agent string matches the IOS version given.
     */
    public static function isIosVersion($major, $user_agent_string)
    {
        $matches = array();
        if (preg_match(self::IOS_VERSION_REGEX, $user_agent_string, $matches)) {
            if ($matches[1] == $major) {
                return true;
            }
        }
        return false;
    }


    /**
     * Is the user agent safari.
     *
     * @param string $user_agent_string
     *   User agent string to test.
     * @return bool
     *   True if user agent is safari.
     */
    public static function isSafari($user_agent_string)
    {
        return (bool) preg_match(self::SAFARI_REGEX, $user_agent_string) && !self::isChromiumBased($user_agent_string);
    }

    /**
     * Is the user agent Chromium based
     * @param string $user_agent_string
     *   User agent string to test
     * @return bool
     *   True if the user agent is safari.
     */
    public static function isChromiumBased($user_agent_string)
    {
        return (bool) preg_match(self::CHROME_REGEX, $user_agent_string);
    }


    /**
     * Is the Chrome version at least.
     *
     * @param int $major
     *   Major version number
     * @param string $user_agent_string
     *   User agent string to test.
     *
     * @return bool
     *   True if the user agent string represents chromium of at least the version number given.
     */
    public static function isChromiumVersionAtLeast($major, $user_agent_string)
    {
        $matches = array();
        if (preg_match(self::CHROME_VERSION_REGEX, $user_agent_string, $matches)) {
            return $major <= $matches[1];
        }
        // Return false if user agent doesn't match.
        return false;
    }

    /**
     * Is the user agent UC Browser
     *
     * @param string $user_agent_string
     *   User agent string to test
     *
     * @return bool
     *   True if the user agent is UCBrowser.
     */
    public static function isUCBrowser($user_agent_string)
    {
        return (bool) preg_match(self::UCBROWSER_REGEX, $user_agent_string);
    }


    /**
     * Check for a minamum build number for UC Browser
     *
     * @param int $major
     *   The major version number.
     * @param int $minor
     *   The minor version number.
     * @param int $build
     *   The build number.
     * @param string $user_agent_string
     *   The user agent string to test.
     *
     * @return bool
     *   True if the UC browser version is at least the version specified.
     */
    public static function isUcBrowserVersionAtLeast($major, $minor, $build, $user_agent_string)
    {
        $matches = array();
        if (preg_match(self::UCBROWSER_REGEX, $user_agent_string, $matches)) {
            if ($major != $matches[1]) {
                return ($major < $matches[1]);
            }
            if ($minor != $matches[2]) {
                return ($minor < $matches[2]);
            }
            return $build <= $matches[3];
        }
        // If not UC browser return false.
        return false;
    }
}
