<?php

/**
 * Class SavviiCdnTest
 * Test CdnLinkRewriter class
 */
class SavviiCdnTest extends Warpdrive_UnitTestCase {

    function setUp() {
        parent::setUp();
        update_option( 'siteurl', 'http://example.co.uk/' );
        putenv( 'WARPDRIVE_SYSTEM_NAME=example' );
    }

    function test_rewrite_returns_string() {
        $cdn = new Savvii\CdnLinkRewriter();
        $this->assertInternalType( 'string', $cdn->rewrite( '' ) );
    }

    function test_cdn_rewrites_css_links() {
        // Arrange
        $test_url = 'http://example.co.uk/wp-content/theme/test/style.css';
        $expected = 'http://cdn.example.savviihq.com/wp-content/theme/test/style.css';
        $cdn = new Savvii\CdnLinkRewriter();
        // Act
        $result = $cdn->rewrite( $test_url );
        // Assert
        $this->assertEquals( $expected, $result );
    }

    function test_cdn_removed_query_from_link() {
        // Arrange
        $test_url = 'http://example.co.uk/wp-content/theme/test/style.css?v=1.0.0';
        $expected = 'http://cdn.example.savviihq.com/wp-content/theme/test/style.css';
        $cdn = new Savvii\CdnLinkRewriter();
        // Act
        $result = $cdn->rewrite( $test_url );
        // Assert
        $this->assertEquals( $expected, $result );
    }

    function test_cdn_removes_query_from_link_with_special_version() {
        // Arrange
        $test_url = 'http://example.co.uk/wp-content/theme/test/style.css?v=in_footer';
        $expected = 'http://cdn.example.savviihq.com/wp-content/theme/test/style.css';
        $cdn = new Savvii\CdnLinkRewriter();
        // Act
        $result = $cdn->rewrite( $test_url );
        // Assert
        $this->assertEquals( $expected, $result );
    }

    function test_cdn_removes_query_from_link_with_multiple_vars() {
        // Arrange
        $test_url = 'http://example.co.uk/wp-content/theme/test/style.css?v=in_footer&ver=2.10.0';
        $expected = 'http://cdn.example.savviihq.com/wp-content/theme/test/style.css';
        $cdn = new Savvii\CdnLinkRewriter();
        // Act
        $result = $cdn->rewrite( $test_url );
        // Assert
        $this->assertEquals( $expected, $result );
    }

    function test_cdn_leaves_html_around_static_asset_alone() {
        // Arrange
        $test_url = '<img src="http://example.co.uk/wp-content/theme/test/style.css?v=in_footer&ver=2.18.0&width=200" width="200" />';
        $expected = '<img src="http://cdn.example.savviihq.com/wp-content/theme/test/style.css" width="200" />';
        $cdn = new Savvii\CdnLinkRewriter();
        // Act
        $result = $cdn->rewrite( $test_url );
        // Assert
        $this->assertEquals( $expected, $result );
    }

    function test_cdn_double_extension() {
        // Arrange
        $test_url = 'http://example.co.uk/wp-content/theme/test/style.css.map';
        $expected = 'http://example.co.uk/wp-content/theme/test/style.css.map';
        $cdn = new Savvii\CdnLinkRewriter();
        // Act
        $result = $cdn->rewrite( $test_url );
        // Assert
        $this->assertEquals( $expected, $result );
    }

    function test_domain_regexp() {
        // Arrange
        $expected = 'example\.co\.uk';
        $cdn = new Savvii\CdnLinkRewriter();
        // Act
        $result = $cdn->get_domain_regexp();
        // Assert
        $this->assertEquals( $expected, $result );
    }

    function test_site_path() {
        // Arrange
        $expected = '/';
        $cdn = new Savvii\CdnLinkRewriter();
        // Act
        $result = $cdn->get_site_path_regexp();
        // Assert
        $this->assertEquals( $expected, $result );
    }

    function test_site_path_with_subdirectory() {
        // Arrange
        update_option( 'siteurl', 'http://example.co.uk/word.press/' );
        $expected = '/word\.press/';
        $cdn = new Savvii\CdnLinkRewriter();
        // Act
        $result = $cdn->get_site_path_regexp();
        // Assert
        $this->assertEquals( $expected, $result );
    }
}
