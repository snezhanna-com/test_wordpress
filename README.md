# Instructions 

Use WordPress Nonce an object oriented way


**Installation**:

`git clone https://github.com/snezhanna-com/test_wordpress.git`

`php composer.phar install`


**Directory structure should look something like this:**
    
    /
    |--/lib
    |  |--Nonce.php
    |
    |--/test
    |  |--bootstrap.php
    |  |--NonceTest.php
    |
    |--/vender/
    |
    |--composer.json
    |--composer.phar
    
    
# Using:

Сlass connectivity

    use lib/Nonce
    
Get Nonce with expiry:

    $nonce_obj = new Nonce('doing_some_form_job', $time = 60*60);
    $nonce = $nonce_obj->create_nonce();
    
Verify Nonce:

    $nonce = $_REQUEST['nonce'];
    $nonce_obj = new Nonce('doing_some_form_job');
    if ( $nonce_obj->verify_nonce( $nonce ) )
        //Verified Source 
    else 
        // Unknown Source
        
Create nonce input field:

    //This will echo input field
    echo $nonce_obj->create_nonce_field( $name = '_wpnonce', $referer = true, $echo = true );
    
Create nonce url

    $url = $nonce_obj->create_nonce_url( $url = 'http://w.org' );
    
Check user is coming from another admin page.

     // This will check current url 
     if ($nonce_obj->check_admin_referral())
        //doing it right
     else 
        //doing it wrong
        

#Unit Test

    vendor/bin/phpunit test/NonceTest