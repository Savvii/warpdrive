<?php

use \Savvii\ReadLogs;

class ReadLogsTest extends Warpdrive_UnitTestCase {

    /**
     * @var ReadLogs
     */
    private $subject;

    function setUp() {
        parent::setUp();
        putenv( 'WARPDRIVE_SYSTEM_NAME=FooBar' );
        $this->subject = $this->getMockBuilder( 'Savvii\ReadLogs' )
            ->setMethods( [ 'file_exists', 'file_size', 'file_get_contents' ] )
            ->getMock();
    }

    // clean log name
    // ----------------------------------------------------------------------

    function test_clean_log_name_for_access() {
        $this->assertEquals( 'access', $this->subject->clean_log_name( 'access' ) );
    }

    function test_clean_log_name_for_error() {
        $this->assertEquals( 'error', $this->subject->clean_log_name( 'error' ) );
    }

    function test_clean_log_name_default_to_access() {
        $this->assertEquals( 'access', $this->subject->clean_log_name( 'FooBar' ) );
    }

    // clean lines
    // ----------------------------------------------------------------------

    function test_clean_lines_for_10() {
        $this->assertEquals( 10, $this->subject->clean_lines( 10 ) );
    }

    function test_clean_lines_for_100() {
        $this->assertEquals( 100, $this->subject->clean_lines( 100 ) );
    }

    function test_clean_lines_defaults_to_10() {
        $this->assertEquals( 10, $this->subject->clean_lines( 0 ) );
        $this->assertEquals( 10, $this->subject->clean_lines( 'a' ) );
    }

    // get log lines
    // ----------------------------------------------------------------------

    function test_get_log_lines_tries_to_find_log() {
        $system_name = 'FooBar';
        $this->subject->expects( $this->once() )
            ->method( 'file_exists' )
            ->with( $this->equalTo( "/var/www/{$system_name}/log/{$system_name}.error.log" ) )
            ->will( $this->returnValue( false ) );
        $this->subject->expects( $this->never() )
            ->method( 'file_size' );
        $this->subject->expects( $this->never() )
            ->method( 'file_get_contents' );

        $lines = $this->subject->get_log_lines( 'error', 10 );
        $this->assertCount( 1, $lines );
        $this->assertContains( 'Error log not found', $lines[0] );
    }

    function test_get_log_lines_on_empty_log() {
        $system_name = 'FooBar';
        $this->subject->expects( $this->once() )
            ->method( 'file_exists' )
            ->with( $this->equalTo( "/var/www/{$system_name}/log/{$system_name}.error.log" ) )
            ->will( $this->returnValue( true ) );
        $this->subject->expects( $this->once() )
            ->method( 'file_size' )
            ->with( $this->equalTo( "/var/www/{$system_name}/log/{$system_name}.error.log" ) )
            ->will( $this->returnValue( 0 ) );
        $this->subject->expects( $this->once() )
            ->method( 'file_get_contents' )
            ->with(
                $this->equalTo( "/var/www/{$system_name}/log/{$system_name}.error.log" ),
                $this->equalTo( 0 ),
                $this->equalTo( 10240 )
            )
            ->will( $this->returnValue( '' ) );

        $lines = $this->subject->get_log_lines( 'error', 10 );
        $this->assertCount( 1, $lines );
        $this->assertContains( 'Error log empty', $lines[0] );
    }

    function test_get_log_lines_returns_requested_amount_of_lines() {
        $system_name = 'FooBar';
        $this->subject->expects( $this->once() )
            ->method( 'file_exists' )
            ->with( $this->equalTo( "/var/www/{$system_name}/log/{$system_name}.error.log" ) )
            ->will( $this->returnValue( true ) );
        $this->subject->expects( $this->once() )
            ->method( 'file_size' )
            ->with( $this->equalTo( "/var/www/{$system_name}/log/{$system_name}.error.log" ) )
            ->will( $this->returnValue( 0 ) );
        $this->subject->expects( $this->once() )
            ->method( 'file_get_contents' )
            ->with(
                $this->equalTo( "/var/www/{$system_name}/log/{$system_name}.error.log" ),
                $this->equalTo( 0 ),
                $this->equalTo( 10240 )
            )
            ->will( $this->returnValue( "Line1\nLine2\nLine3\nLine4\nLine5\nLine6\nLine7\nLine8\nLine9\nLine10\nLine11\n" ) );

        $lines = $this->subject->get_log_lines( 'error', 10 );
        $this->assertCount( 10, $lines );
    }

    function test_get_log_lines_returns_lines_newest_first() {
        $system_name = 'FooBar';
        $this->subject->expects( $this->once() )
            ->method( 'file_exists' )
            ->with( $this->equalTo( "/var/www/{$system_name}/log/{$system_name}.error.log" ) )
            ->will( $this->returnValue( true ) );
        $this->subject->expects( $this->once() )
            ->method( 'file_size' )
            ->with( $this->equalTo( "/var/www/{$system_name}/log/{$system_name}.error.log" ) )
            ->will( $this->returnValue( 0 ) );
        $this->subject->expects( $this->once() )
            ->method( 'file_get_contents' )
            ->with(
                $this->equalTo( "/var/www/{$system_name}/log/{$system_name}.error.log" ),
                $this->equalTo( 0 ),
                $this->equalTo( 10240 )
            )
            ->will( $this->returnValue( "Line1\nLine2\nLine3\nLine4\nLine5\nLine6\nLine7\nLine8\nLine9\nLine10\nLine11\n" ) );

        $lines = $this->subject->get_log_lines( 'error', 10 );
        $this->assertCount( 10, $lines );
        $this->assertEquals( 'Line11', $lines[0] );
    }
}
