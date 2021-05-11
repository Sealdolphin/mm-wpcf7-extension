<?php
class Custom_Validation {

    private static $phone_regex = "/\\+36-\\d{2}-\\d{3}-\\d{4}/";

    function __construct() {
        if(class_exists("WPCF7_Validation")) {
            add_filter( 'wpcf7_validate_tel*' , array($this, "apply_phone_validation"), 20, 2 );
        }
    }

    function apply_phone_validation( $result, $tag )
    {
        $phone_number = isset ( $_POST[$tag->name] ) ? trim( $_POST[$tag->name] ) : "";

        if(!preg_match(self::$phone_regex, $phone_number)) {
            $result->invalidate($tag, "Kérlek kövesd a formátumot!");
        }

        return $result;
    }

}