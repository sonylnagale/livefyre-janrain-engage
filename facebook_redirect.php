<?php
/*
Purpose: Provide a landing page for the last step of successful oAuth
         that is on the correct (Facebook approved) domain.

Location: This file should be hosted on the same domain as your 
          Facebook App's "site url".

Input Parameters: 
    - (GET) lfoauth: This should be a url-safe base64-encoded URL that 
      is the "real" final redirection URL. Livefyre sets this.

      For testing purposes, this can be set to a url-safe base64-encoded 
      URL of your choosing, but the domain name must end in .fyre.co in 
      order to be considered valid.

    - (GET) [all other parameters]: Any other parameters should simply 
      be passed thru on the redirection URL. If the decoded URL from "lfoauth" 
      includes querystring parameters, then the additional parameters included 
      with the initial request should be appended with "&..."

Output: The response should indicate that the browser redirect (302) to the 
        "real" URL which is encoded in the "lfoauth" parameter.

*/

function base64url_decode($data) {
  return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT));
}

if (isset($_GET['lfoauth'])) {
    // warning: If implemented with non-PHP, b64 may fail because we have 
    //          stripped padding (trailing ='s), to make Facebook happy.  
    //          The decode needs to be non-strict in this sense.

    $rdir = base64url_decode($_GET['lfoauth']);

    // validate the destination to secure this proxy
    preg_match("/^(http:\/\/)?([^\/?]+)/i", $rdir, $domain_only);   
    $host = $domain_only[2];
    if (!strstr($host,'fyre.co')) {
        echo "Error - this redirection is not allowed! ".$host;
        exit;
    }

    // if params were included in the uri already, append with &, otherwise ?
    $sep = strstr($rdir,'?') ? '&' : '?';

    // don't include this in the params we add to the redirect url
    unset($_GET['lfoauth']);

    // assemble a new querystring from the remaining inbound GET params
    $rdir = $rdir.$sep.http_build_query($_GET);

    // this does the actual redirection, PHP's implementation is weird
    header('Location: '.$rdir);
}
?>