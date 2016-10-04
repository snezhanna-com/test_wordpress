<?php

namespace lib;

/**
 * Class Nonce
 *
 * Simplified nonce usage
 *
 * @package lib
 */
class Nonce{
    /**
     * @var string $action that is being performed.
     */
    private $action;
    /**
     * @var int $nonce expire time
     */
    private $expire;


    /**
     * @param int|string $action
     * @param int $expire Life of nonce in second, default seconds in one day
     */
    function __construct( $action = - 1, $expire = 86400 ) {
        $this->action = $action;
        $this->expire = $expire;
    }


    /**
     * Get expire value
     * @return int
     */
    public function get_expire() {
        return $this->expire;
    }
    /**
     * @return Nonce
     */
    public function get_action() {
        return $this->action;
    }



    /**
     * @param $expire
     * @return int
     */
    function set_expire( $expire ) {
        return $this->get_expire();
    }

    /**
     * @return bool
     */
    private function is_hookable()  {
        return ( 86400 !== $this->get_expire() );
    }
    /**
     * Set hook to nonce_life is is_hookable
     */
    protected function set_hook() {
        if ( $this->is_hookable() ) {
            add_filter( 'nonce_life', array( $this, 'set_expire' ), 1 );
        }
    }
    /**
     * Remove hook from `nonce_life` after nonce is generated or verified in case of is hookable
     */
    protected function remove_hook() {
        if ( $this->is_hookable() ) {
            remove_filter( 'nonce_life', array( $this, 'set_expire' ), 1 );
        }
    }


    /**
     * Create nonce
     * @return string
     *
     */
    public function create_nonce() {
        $this->set_hook();
        $nonce = wp_create_nonce( $this->action );
        $this->remove_hook();
        return $nonce;
    }

    /**
     * Verify nonce
     *
     * @param String $nonce Nonce to verify.
     * @return false|int
     */
    public function verify_nonce( $nonce ) {
        $this->set_hook();
        $is_valid_nonce = wp_verify_nonce( $nonce, $this->get_action() );
        $this->remove_hook();
        return $is_valid_nonce;
    }
    /**
     * Create nonce field
     *
     * @param string $name Optional. Nonce name. Default '_wpnonce'.
     * @param bool $referer Optional. Whether to set the referer field for validation. Default true.
     * @param bool $echo Optional. Whether to display or return hidden form field. Default true.
     *
     * @return string Nonce field HTML markup.
     */
    public function create_nonce_field( $name = '_wpnonce', $referer = true, $echo = true ) {
        $this->set_hook();
        $nonce_field = wp_nonce_field( $this->get_action(), $name, $referer, $echo );
        $this->remove_hook();
        return $nonce_field;
    }

    /**
     * Get nonce url
     *
     * @param string $actionurl URL to add nonce action.
     * @param string $name Optional. Nonce name. Default '_wpnonce'.
     *
     * @return string Escaped URL with nonce action added.
     */
    public function create_nonce_url( $actionurl, $name = '_wpnonce' ) {
        $this->set_hook();
        $nonce_url = wp_nonce_url( $actionurl, $this->get_action(), $name );
        $this->remove_hook();
        return $nonce_url;
    }
    /**
     * Check user is coming from another admin page
     *
     * @param string $query_arg Optional. Key to check for nonce in `$_REQUEST`.
     *                              Default '_wpnonce'.
     *
     * @return false|int False if the nonce is invalid, 1 if the nonce is valid and generated between
     *                   0-12 hours ago, 2 if the nonce is valid and generated between 12-24 hours ago.
     */
    public function check_admin_referral( $query_arg = '_wpnonce' ) {
        $this->set_hook();
        $is_valid_admin_referer = check_admin_referer( $this->get_action(), $query_arg );
        $this->remove_hook();
        return $is_valid_admin_referer;
    }

    /**
     * Check ajax referer
     *
     * @param bool $query_arg
     * @param bool $die
     *
     * @return false|int
     */
    public function check_ajax_referer( $query_arg = false, $die = true ) {
        $this->set_hook();
        $is_valid_ajax_reference = check_ajax_referer( $this->get_action(), $query_arg, $die );
        $this->remove_hook();
        return $is_valid_ajax_reference;
    }

    /**
     * Display 'Are You Sure' message to confirm the action being taken.
     */
    public function nonce_ays() {
        wp_nonce_ays( $this->get_action() );
    }

    /**
     * Get the time-dependent variable for nonce creation.
     * @return int
     */
    public function nonce_tick() {
        return wp_nonce_tick();
    }
}

