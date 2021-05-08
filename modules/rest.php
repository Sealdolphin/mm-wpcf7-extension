<?php
class Flamingo_REST {

    private bool $enabled;
    
    function __construct($enabled = false) {
        $this->enabled = $enabled;
        if($this->enabled) {
            add_action("rest_api_init", array( $this, "create_flamingo_enpoints"));
        }
    }

    function create_endpoint_credentials() {

    }

    function create_flamingo_enpoints()
    {
        //Register get messages
        register_rest_route("flamingoext/v1", "/messages", array(
            "methods" => "GET",
            "callback" => array($this, "get_flamingo_messages"),
            "permission_callback" => function () { return current_user_can("manage_options"); }
        ));
    }

    function get_flamingo_messages( WP_REST_Request $request)
    {
        if($this->enabled) {
            //Return with all messages

            $messages = array();

            return new WP_REST_Response(
                array(
                    "status" => 200,
                    "response" => __("Sucesss"),
                    "body_response" => $messages
                )
            );
        } else {
            //Return with Error
            return new WP_Error(404, __("Not found"), __("Flamingo plugin is not installed"));
        }
    }

}