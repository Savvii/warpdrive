<?php

use Savvii\Options;

class SavviiOptionsTest extends Warpdrive_UnitTestCase {

    public function test_api_url_from_environment() {
        $location = 'https://example.org';
        putenv( 'WARPDRIVE_API=' . $location );
        $this->assertEquals( $location, Options::api_location() );
    }

    public function test_access_token_from_environment() {
        putenv( 'WARPDRIVE_ACCESS_TOKEN=Foo42Bar' );
        $this->assertEquals( 'Foo42Bar', Options::access_token() );
    }

    public function test_system_name_from_environment() {
        putenv( 'WARPDRIVE_SYSTEM_NAME=FooSystemBar' );
        $this->assertEquals( 'FooSystemBar', Options::system_name() );
    }
}
