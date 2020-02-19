# SAME SITE EXCEPTION PHP LIBRARY
This is a light weight library you can include if you need to set samesite cookies but also need to account for older browsers that have issues with accepting them.

There is currently a backwards compatible breaking change in the way Chrome 70+ handles cookies, this means that you
have to set SameSite=None as a cookie header for some cross site cookies to work in Chrome, but some older browsers will
reject this cookie and fail to work. This library will set an appropriate SameSite header for the current user agent.

It is a PHP version of the pseudocode outlined [here](https://www.chromium.org/updates/same-site/incompatible-clients)

# Usage

To include this in in your application type `composer require fullfatthings/samesiteexceptions`

Then when you are setting a cookie with a same site value use

`$safe_value = \FullFatThings\SameSiteException\SameSiteException::getSafeString($same_site_value);`

If the safe value returned is null you should not add a samesite value (even blank) to the cookie you are setting.

