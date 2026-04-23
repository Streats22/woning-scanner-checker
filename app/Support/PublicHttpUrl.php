<?php

namespace App\Support;

/**
 * Validates that a URL is safe for server-side HTTP fetch (SSRF mitigation: no link-local, private, or metadata IP targets).
 * Redirect targets must be re-validated by the HTTP client (see ListingFetchService).
 */
final class PublicHttpUrl
{
    /**
     * For use in HTML href attributes: http(s) only, no creds, block literal private IPs. No live DNS
     * (avoids a lookup on every page view); do not use for server-side fetch.
     */
    public static function isHttpOrHttpsForHref(?string $url): bool
    {
        if ($url === null || $url === '') {
            return false;
        }
        if (filter_var($url, FILTER_VALIDATE_URL) === false) {
            return false;
        }
        $scheme = strtolower((string)parse_url($url, PHP_URL_SCHEME));
        if (!in_array($scheme, ['http', 'https'], true)) {
            return false;
        }
        if (self::userOrPasswordInUrl($url)) {
            return false;
        }
        $host = parse_url($url, PHP_URL_HOST);
        if (!is_string($host) || $host === '') {
            return false;
        }
        $host = strtolower($host);
        if (str_ends_with($host, '.test') || $host === 'localhost') {
            return false;
        }
        if (filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            return self::isPublicIpv4($host);
        }
        if (filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            return self::isPublicIpv6($host);
        }

        return true;
    }

    public static function isSafeForServerFetch(string $url): bool
    {
        if (filter_var($url, FILTER_VALIDATE_URL) === false) {
            return false;
        }
        if (self::userOrPasswordInUrl($url)) {
            return false;
        }
        $scheme = strtolower((string)parse_url($url, PHP_URL_SCHEME));
        if (!in_array($scheme, ['http', 'https'], true)) {
            return false;
        }
        if (self::hostResolvesToNonPublic($url)) {
            return false;
        }

        return true;
    }

    private static function userOrPasswordInUrl(string $url): bool
    {
        return parse_url($url, PHP_URL_USER) !== null
            || parse_url($url, PHP_URL_PASS) !== null;
    }

    private static function hostResolvesToNonPublic(string $url): bool
    {
        $host = parse_url($url, PHP_URL_HOST);
        if (!is_string($host) || $host === '') {
            return true;
        }
        $host = strtolower($host);
        if (str_contains($host, "\0") || str_contains($host, '/')) {
            return true;
        }
        if (str_ends_with($host, '.test') || $host === 'localhost') {
            return true;
        }
        if (filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            return !self::isPublicIpv4($host);
        }
        if (filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            return !self::isPublicIpv6($host);
        }
        $ips = self::resolveHostIps($host);
        if ($ips === []) {
            return true;
        }
        foreach ($ips as $ip) {
            if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) && !self::isPublicIpv4($ip)) {
                return true;
            }
            if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) && !self::isPublicIpv6($ip)) {
                return true;
            }
        }

        return false;
    }

    private static function resolveHostIps(string $host): array
    {
        $out = [];
        if (function_exists('dns_get_record')) {
            foreach (@dns_get_record($host, DNS_A) ?: [] as $row) {
                if (!empty($row['ip'])) {
                    $out[] = $row['ip'];
                }
            }
            foreach (@dns_get_record($host, DNS_AAAA) ?: [] as $row) {
                if (!empty($row['ipv6'])) {
                    $out[] = $row['ipv6'];
                }
            }
        }
        if ($out !== []) {
            return array_values(array_unique($out));
        }
        $a = gethostbynamel($host);
        if (is_array($a)) {
            return array_values(array_unique($a));
        }

        return [];
    }

    private static function isPublicIpv4(string $ip): bool
    {
        return (bool)filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE);
    }

    private static function isPublicIpv6(string $ip): bool
    {
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) === false) {
            return false;
        }
        if ($ip === '::1' || str_starts_with($ip, '::ffff:127.')) {
            return false;
        }
        if (str_starts_with($ip, 'fe80:') || str_starts_with($ip, 'fc00:') || str_starts_with($ip, 'fd00:') || $ip === '::') {
            return false;
        }

        return (bool)filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6 | FILTER_FLAG_NO_PRIV_RANGE);
    }
}
