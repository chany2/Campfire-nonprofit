<?php
// The MIT License (MIT)
//
// Copyright (C) 2013 TrendSpottr
//
// Permission is hereby granted, free of charge, to any person obtaining a copy
// of this software and associated documentation files (the "Software"), to deal
// in the Software without restriction, including without limitation the rights
// to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
// copies of the Software, and to permit persons to whom the Software is
// furnished to do so, subject to the following conditions:
//
// The above copyright notice and this permission notice shall be included in
// all copies or substantial portions of the Software.
//
// THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
// IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
// FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
// AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
// LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
// OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
// THE SOFTWARE.

// NOTE:  Before you can run this file successfully, you must edit the third line in the
//        alertsAPIRequest function below and insert your TrendSpottr API Key.  That key
//        can be found by logging into your TrendSpottr.com account, then clicking on your
//        email address in the upper right of any TrendSpottr and selecting "Edit Account"
//        from the menu that will drop down. If you don't do that, the first API call made
//        by this script will fail with an invalid api key, and the script will abort.

try
{
    // Build the query string
    $parameters = array(
                    // Complex query string
        "q"      => "Technology OR #technology OR Tech OR #tech OR Gadgets OR #Mobile OR #apps OR #technews OR Tablets OR #EdTech OR #smartphones",
        "n"      => "20",                        // Return 20 results
        "expand" => "true",                      // Provide full URL details
        // "g"      => "40.72251,-74.020128,100km",// Within 100km of New York Airport
    );

    $results = searchAPIRequest($parameters);

    // Print some stats.
    // printf("%d links found %s oeembed data\n",
    //         count($results['links']),
    //         isset($results['links'][0]['expanded']) ? "with" : "without");
    // printf("%d hashtags found\n", count($results['hashtags']));
    // printf("%d sources found\n",  count($results['sources']));
    // printf("%d phrases found\n",  count($results['phrases']));

    // Dump the results to standard output

    $results = array_splice($results, 0, 1);
    $results = $results["links"];

    $max = sizeOf($results);
    for ($i=0; $i<$max; $i++) {
        print_r($results[$i]["expanded"]["title"]);
        print_r("\r\n");
        print_r($results[$i]["expanded"]["url"]);
        print_r("\r\n");
        print_r($results[$i]["expanded"]["thumbnail_url"]);
        print_r("\r\n\r\n");
    }

} catch (Exception $e)
{
    echo "Error: " . $e->getMessage() . PHP_EOL;
}

// End of mainline
exit;

// Helper functions
// Function to call the Trendspottr API via CURL, and check for
// authentication and curl errors.
function searchAPIRequest ($parameters)
{
    $serverURL   = 'http://api.trendspottr.com/v1.5/search.php?';
    $apiKey      = 'cd72c0df945d7b4709269c70d7daab5f';
    $validateSSL = false;

    // Add the API Key to the list of provided parameters
    $parameters['key']    = $apiKey;

    // Format the full url we are going to call
    $url = $serverURL . http_build_query($parameters);

    // Initialize the PHP curl agent
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_USERAGENT, "curl");
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_FAILONERROR, true);
    curl_setopt($ch, CURLOPT_HTTPGET, true);
    // curl_setopt($ch, CURLOPT_VERBOSE, true);

    if ($validateSSL)
    {
        // The libcurl included in PHP does not come with any of the certificates needed to
        // validate SSL connections built in.  Before you can validate SSL certificates, you
        // must download a current version, install it on your system and change the path in
        // the line below to point to that file.  You can find a pre-built version of the
        // certificates bundle at
        //   http://curl.haxx.se/docs/caextract.html
        // haxx.se is the group responsible for libcurl.  If you don't trust this, you can
        // get the file used by the current version of Firefox from Mozilla.org, but will be
        // in the wrong format. You must convert it to .pem format before it can be used by libcurl.
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_CAINFO, "/path/to/ca-bundle.crt");
    } else
    {
        // Disable SSL verification.  This is NOT recommended.
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    }

    $result = curl_exec($ch);
    if ($result === false)
        throw new Exception ("curl Error: " . curl_error($ch));

    $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    if ($http_status != 200)
        throw new Exception("Request Failed. http status: " . $http_status);

    curl_close($ch);

    // Trim any whitespace from the front and end of the string and decode it
    $result = json_decode(trim($result), true);
    if (($error = get_json_error()) !== false) {
        throw new Exception("json_decode failed: " . $error);
    }

    if (isset($result['error']))
    {
        // Failed
        throw new Exception($result['error']['code'] . ": " . $result['error']['errstring'] . PHP_EOL);
    }

    // We got a valid result, verify that.
    if (!isset($result['results']))
    {
        throw new Exception("json_decode did not return 'results'");
        exit;
    }

    // The API call succeeded, return results
    return $result['results'];
}

// Function to provide informative error message if the
// json decode fails - should never happen, but ...
function get_json_error()
{
    switch (json_last_error())
    {
        case JSON_ERROR_NONE:
            return false;
            break;
        case JSON_ERROR_DEPTH:
            return 'Maximum stack depth exceeded';
            break;
        case JSON_ERROR_STATE_MISMATCH:
            return 'Underflow or the modes mismatch';
            break;
        case JSON_ERROR_CTRL_CHAR:
            return 'Unexpected control character found';
            break;
        case JSON_ERROR_SYNTAX:
            return 'Syntax error, malformed JSON';
            break;
        case JSON_ERROR_UTF8:
            return 'Malformed UTF-8 characters, possibly incorrectly encoded';
            break;
    }
    return 'Unknown error';
}
?>
