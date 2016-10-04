<?php
use lib\Nonce;
use Brain\Monkey;
use Brain\Monkey\Functions;

class NonceTest extends PHPUnit_Framework_TestCase {

    protected $base_url;
    protected $nonce_action;
    protected $nonce_name;
    protected $nonce_value;
    protected $nonce_url;
    protected $nonce_field;

    protected function setUp() {
        parent::setUp();
        Monkey::setUpWP();

        $this->base_url = 'http://w.org';
        $this->nonce_action = 'action';
        $this->nonce_name = '_wpnonce';
        $this->nonce_value = 'ad4g4dclean';
        $this->nonce_url = $this->base_url . '?' . $this->nonce_name . '=' . $this->nonce_value;
        $this->nonce_field = '<input type="hidden" id="' . $this->nonce_name . '" name="' . $this->nonce_name . '" value="' . $this->nonce_value . '" />';
    }


    protected function tearDown() {
        Monkey::setUpWP();
        parent::tearDown();
    }


    function test_nonce_create_verify() {
        //Setup
        Functions::expect( 'wp_create_nonce' )
            ->once()
            ->with( 'doing_create_and_verfiy_test' )
            ->andReturn( $this->nonce_value  );
        Functions::expect( 'wp_verify_nonce' )
            ->once()
            ->with( $this->nonce_value , 'doing_create_and_verfiy_test' )
            ->andReturn( 1 );
        Functions::expect( 'wp_verify_nonce' )
            ->once()
            ->with( $this->nonce_value.'_wrong', 'doing_create_and_verfiy_test' )
            ->andReturn( false );
        //Act
        $nonce         = new Nonce( 'doing_create_and_verfiy_test' );
        $nonce_val     = $nonce->create_nonce();
        $none_accepted = $nonce->verify_nonce( $nonce_val );
        $none_rejected = $nonce->verify_nonce( $nonce_val . '_wrong' );
        //Verify
        $this->assertEquals( $none_accepted, 1 );
        $this->assertFalse( $none_rejected, 0 );
    }


    function test_create_nonce_field() {

        Functions::expect( 'wp_verify_nonce' )
            ->once()
            ->with( $this->nonce_value , 'clean_field' )
            ->andReturn( 1 );
        Functions::expect( 'wp_nonce_field' )
            ->once()
            ->with( 'clean_field', $this->nonce_name , false, false )
            ->andReturn( $this->nonce_field  );

        $nonce            = new Nonce( 'clean_field' );
        $html_input_field = $nonce->create_nonce_field( $this->nonce_name, false, false );
        $dom              = new DOMDocument();
        $dom->loadHTML( $html_input_field );
        $inputs    = $dom->getElementsByTagName( 'input' )->item( 0 );
        $nonce_val = $inputs->getAttribute( 'value' );

        $this->assertEquals( $nonce->verify_nonce( $nonce_val ), 1 );
    }


    function test_create_nonce_url() {

        Functions::expect( 'wp_nonce_url' )
            ->once()
            ->with( $this->base_url, 'clean_url', $this->nonce_name )
            ->andReturn( $this->nonce_url );
        Functions::expect( 'wp_verify_nonce' )
            ->once()
            ->with( $this->nonce_value, 'clean_url' )
            ->andReturn( 1 );

        // get url with nonce field
        $nonce = new Nonce( 'clean_url' );
        $url   = $nonce->create_nonce_url( $this->base_url );
        $query = parse_url( $url );
        $q     = array( $this->nonce_name );
        parse_str( $query['query'], $q );

        $this->assertEquals( $nonce->verify_nonce( str_replace( '"', '', $q[$this->nonce_name] ) ), 1 );
    }


    function test_check_admin_referral() {

        Functions::expect( 'wp_create_nonce' )
            ->once()
            ->with( 'doing_some_admin_job' )
            ->andReturn( $this->nonce_value );
        Functions::expect( 'check_admin_referer' )
            ->once()
            ->with( 'doing_some_admin_job', $this->nonce_name )
            ->andReturn( 1 );

        $nonce                = new Nonce( 'doing_some_admin_job' );
        $nonce_val            = $nonce->create_nonce();
        $_REQUEST[ $this->nonce_name ] = $nonce_val;

        $this->assertEquals( $nonce->check_admin_referral(), 1 );
    }
}
