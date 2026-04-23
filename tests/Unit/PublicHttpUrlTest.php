<?php

namespace Tests\Unit;

use App\Support\PublicHttpUrl;
use Tests\TestCase;

class PublicHttpUrlTest extends TestCase
{
    public function test_href_allows_http_https_public(): void
    {
        $this->assertTrue(PublicHttpUrl::isHttpOrHttpsForHref('https://www.example.com/path?q=1'));
    }

    public function test_href_rejects_empty_and_null(): void
    {
        $this->assertFalse(PublicHttpUrl::isHttpOrHttpsForHref(''));
        $this->assertFalse(PublicHttpUrl::isHttpOrHttpsForHref(null));
    }

    public function test_href_rejects_javascript(): void
    {
        $this->assertFalse(PublicHttpUrl::isHttpOrHttpsForHref('javascript:alert(1)'));
    }

    public function test_href_rejects_credential_bearing_url(): void
    {
        $this->assertFalse(PublicHttpUrl::isHttpOrHttpsForHref('https://user:pass@example.com/'));
    }

    public function test_href_rejects_localhost_and_dot_test(): void
    {
        $this->assertFalse(PublicHttpUrl::isHttpOrHttpsForHref('http://localhost/'));
        $this->assertFalse(PublicHttpUrl::isHttpOrHttpsForHref('http://app.test/'));
    }

    public function test_href_rejects_private_literal_ipv4(): void
    {
        $this->assertFalse(PublicHttpUrl::isHttpOrHttpsForHref('http://192.168.1.1/'));
    }

    public function test_href_allows_public_literal_ipv4(): void
    {
        $this->assertTrue(PublicHttpUrl::isHttpOrHttpsForHref('http://8.8.8.8/'));
    }

    public function test_server_fetch_rejects_private_literal_ipv4(): void
    {
        $this->assertFalse(PublicHttpUrl::isSafeForServerFetch('http://192.168.0.1/x'));
    }

    public function test_server_fetch_allows_public_literal_ip_without_dns(): void
    {
        $this->assertTrue(PublicHttpUrl::isSafeForServerFetch('http://8.8.8.8/'));
    }

    public function test_server_fetch_rejects_user_in_url(): void
    {
        $this->assertFalse(PublicHttpUrl::isSafeForServerFetch('https://u:p@8.8.8.8/'));
    }
}
