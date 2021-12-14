<?php
// Name: Simple SAML inline Hook Example
// 
// Purpose: To explain how SAML inline Hooks work without getting caught up 
//          in the code.
//
//          This is a simple PHP example that will respond to the request
//.         from a SAML InLine Hook request. 
//
// Author: Jeff Nester
//
// List the headers so you can see what is being passed. All output goes 
// to the web server default logs. i.e. /var/www/log/errors
//
error_log("--> SAML_INLINE_HOOK ----- " . "|------------- Start of hook.php ------------------------- ");
error_log("--> SAML_INLINE_HOOK ----- " . "| Headers: ");

// Fetch headers for display
$headers = getallheaders ();

// write headers to error log
foreach ( $headers as $key=>$val) {
   error_log("--> SAML_INLINE_HOOK ----- |    " . $key . ": " . $val);
}

// Obtain the JSON from the request
$data = json_decode(file_get_contents('php://input'), true);
error_log("--> SAML_INLINE_HOOK ----- " . "|");
error_log ( "--> SAML_INLINE_HOOK ----- " . "| Event: " . $data['eventType'] );

// from the data extract the user for the request and write to the log
$user = $data['data']['context']['user'];
$username = $user['profile']['login'];
error_log ( "--> SAML_INLINE_HOOK ----- " . "| Username: " . $username );

// Construct the return JSON. This has to be perfect or Okta will ignore it

// in order to get the arrays correct in PHP I have used this tecnhique
// of putting the major arrays in another variable and then including them

// Values array of the work to do
$values[] = array(
                "op" => "add",
                "path" => "/claims/hookAttribute",
                "value" => array(
                                "attributes" => array(
                                    "NameFormat" => "urn:oasis:names:tc:SAML:2.0:attrname-format:basic"
                                ),
                                "attributeValues" => array(
                                                            array(
                                                                "attributes" => array(
                                                                    "xsi:type" => "xs:string"
                                                                ),
                                                                "value" => "NOT_STORED_IN_OKTA"                                 
                                                            )
                                                        )
                                )
                );

// Creates the array that has commands 
$commands[] = array (
                        "type" => "com.okta.assertion.patch",
                        "value" => $values  // <- values is from above
        ); 

// By creating a standard class for this object the warning message: 
//    Creating default object from empty value 
// will not happen
$obj = new stdClass();

// Build the entire object
$obj->commands = $commands;

// Convert the object to a JSON string
$myJSON = json_encode($obj,JSON_UNESCAPED_SLASHES);

// print the response to the error_log so that you can see what 
// what the final return value is
error_log("--> SAML_INLINE_HOOK ----- " . "|");
error_log("--> SAML_INLINE_HOOK ----- " . "|---- Returned JSON ----" );
error_log($myJSON);
error_log("--> SAML_INLINE_HOOK ----- " . "|");

// Respond to Okta with the JSON string
echo $myJSON;
error_log("--> SAML_INLINE_HOOK ----- " . "|------------- End of hook.php ------------------------- ");
error_log("--> SAML_INLINE_HOOK ----- " . "|");
?>
