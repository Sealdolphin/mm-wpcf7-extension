<?php
class Flamingo_REST_Module {

    private static $flamingo_plugin = "flamingo/flamingo.php";
    
    function __construct() {
        add_action("rest_api_init", array( $this, "create_flamingo_enpoints"));
    }

    function create_endpoint_credentials() {

    }

    function check_for_flamingo() {
        return is_plugin_active( self::$flamingo_plugin );
    }

    function create_flamingo_enpoints()
    {
        //Register get messages
        register_rest_route("flamingoext/v1", "/messages", array(
            "methods" => "GET",
            "callback" => array($this, "get_flamingo_messages"),
            "permission_callback" => function () { return current_user_can("manage_options"); }
        ));
        //Register get messages with filter
        register_rest_route("flamingoext/v1", "/messages/(?P<form>.+)", array(
            "methods" => "GET",
            "callback" => array($this, "get_flamingo_messages_filtered"),
            "permission_callback" => function () { return current_user_can("manage_options"); }
        ));
    }

    function get_flamingo_messages( WP_REST_Request $request, $filter = NULL )
    {
        try {
            $posts = get_posts(
                array(
                    "post_type" => "flamingo_inbound"
                )
            );
    
            if($filter) {
                $posts = array_filter($posts, function($p) use($filter) {
                    return ($p->post_title === $filter);
                });
            }
    
            $messages = array_map(array($this, "convert_post"), $posts);
    
            return new WP_REST_Response(
                array(
                    "status" => 200,
                    "response" => __("OK"),
                    "body_response" => $messages
                )
            );
        } catch (Exception $error) {
            return new WP_Error(500, __("Internal server error"));
        }
        
    }

    function get_flamingo_messages_filtered( WP_REST_Request $request ) {
        return $this->get_flamingo_messages($request, $request->get_param("form"));
    }

    private function convert_post($post)
    {
        if (class_exists("Flamingo_Inbound_Message")) {
            $flamingo = new Flamingo_Inbound_Message($post);

            $message_array = array(
                "ID" => $flamingo->id(),
                "form_title" => $flamingo->subject,
                "timestamp" => $post->post_date,
                "name" => $flamingo->from_name,
                "email" => $flamingo->from_email,
                "response" => $flamingo->fields
            );
    
            return $message_array;
        } else {
            throw new Exception("Flamingo is not installed", 1);
        }
    }

}