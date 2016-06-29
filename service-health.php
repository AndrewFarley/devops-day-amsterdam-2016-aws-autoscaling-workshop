<?php

$_SERVER['HTTPS'] = 'on';
$GLOBALS['failed'] = FALSE;
$GLOBALS['failures'] = array();
    
// Do a manual header override requiring a username and password...
/*
if ( ( empty($_REQUEST['password']) || $_REQUEST['password'] != 'awsr0cks') && 
     (empty($_SERVER['PHP_AUTH_PW']) || $_SERVER['PHP_AUTH_PW'] != 'awsr0cks') ) {
    header( "www-authenticate: Basic realm=\"$realm\"" );
    header( "HTTP/1.0 401 Unauthorized" );
    echo "HTTP/1.0 401 Unauthorized\nAccess Denied and logged.  Please login or go away.\n";
    exit;
}
*/

// trigger_error("Service-Health started at ".date('M-d-y H:i:s')." on ".getActualHostname()." by ".getActualRemoteIP(), E_USER_WARNING); 

// Load DB Configuration
// $database_settings = require('application/settings/database.php');

// print_r($database_settings); exit;

?><!DOCTYPE html><html><head>
<meta http-equiv="expires" content="0">
<meta http-equiv="pragma" content="no-cache">
<meta http-equiv="cache-control" content="no-cache, must-revalidate">
<title>Service Health as of <?php echo date('M-d-y H:i:s'); ?></title>
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.8.2/jquery.min.js"></script>
</head><body><h1>Service Health for <?php echo getActualHostname(); ?> as of <?php echo date('M-d-y H:i:s'); ?></h1>

<?php


// TODO FARLEY, WHEN/IF needed
// echo displayTestResults("That memcached works (get, set, delete)", function () {
//     // Check we can set cache
//     $result = MultiCache::cache_set('test-one', 123);
//     if ($result) {
//         // Clear from local cache, to check if memcache is working
//         $result = MultiCache::cache_delete('test-one', FALSE);
//         // Try to get from memcached
//         if ($result) {
//             $temp = MultiCache::cache_get('test-one');
//             if ($temp == 123) {
//                 // Then ensure we can actually delete
//                 $result = MultiCache::cache_delete('test-one');
//                 $temp = MultiCache::cache_get('test-one');
//                 if ($temp === NULL) {
//                     return true;
//                 }
//             }
//         }
//     }
//     
//     // If anything fails, we fail
//     return false;
// });


/*
echo displayTestResults("That the database (".$database_settings['params']['dbname'].") is accessible at ".$database_settings['params']['host'], function () {
    
    // Load DB Configuration
    $database_settings = require('application/settings/database.php');
    
    // Try to connect to the server
    $link = mysqli_connect($database_settings['params']['host'], $database_settings['params']['username'], $database_settings['params']['password']);
    if (!$link) {
        die('here link');
        return false;
    }
    
    // Try to connect to our database
    $db_selected = mysqli_select_db($link, $database_settings['params']['dbname']);
    if (!$db_selected) {
        die('heredb');
        return false;
    }

    // If we succeeded, then we're good
    return true;
});
*/




echo displayTestResults("That this server's CPU is not overloaded", function () {
    /**
     * Get our load average
     */
    $result = @exec('uptime');
    $strip_before = 'load average:';
    $skip = stripos($result,$strip_before) + strlen($strip_before);
    $load = trim(substr($result,$skip));
    $load = explode(', ',$load);
    if (count($load) != 3)
        return false;
    
    /**
     * Try to get our CPU count...
     */
    // First try to get cached value from temp folder
    $path = get_tmp_path().'/cpucount';
    if (is_file($path) && is_readable($path))
        $count = file_get_contents($path);
    // If that failed (check here) then try to get it from the cli
    if (!isset($count) || !is_numeric($count))
        $count = @exec('cat /proc/cpuinfo | grep "processor" | wc -l');
    // If we don't know the cpu count, then we'll fail gracefully, not able to use our serverload detection
    if (!isset($count) || !is_numeric($count))
        return false;
    // If we got it, save it in the temp folder, so we don't have to get it again (this reboot)
    else
        if (!is_file($path))
            file_put_contents($path,$count);

    // Check how high our 5-minute CPU load is (> 1000%?)
    if ($load[1] / $count > 10.00) {
        return false;
    } else {
        return true;
    }
});

echo displayTestResults("That this server's disk space is not full (< 256MB free)", function () {
    $a = disk_free_space('/');
    if (!is_numeric($a) || $a < (1024 * 1024 * 256)) {
        return false;
    }
    $a = disk_free_space('/tmp');
    if (!is_numeric($a) || $a < (1024 * 1024 * 256)) {
        return false;
    }
    $a = disk_free_space('/var');
    if (!is_numeric($a) || $a < (1024 * 1024 * 256)) {
        return false;
    }
    $a = disk_free_space('/var/log');
    if (!is_numeric($a) || $a < (1024 * 1024 * 256)) {
        return false;
    }
    return true;
});


echo displayTestResults("That the homepage loads properly from http://localhost/", function () {
    $url = 'http://localhost/?cache_busting='. mt_rand(1,500000);
    $result = curlRequest($url, 'GET', array(), false, false, 9);
    if (stripos($result, "Thanks for coming") !== FALSE) {
        return true;
    } else {
        return false;
    }
});

?>
</body></html>
<?php

$http_headers['200']  = "HTTP/1.0 200 OK";
$http_headers['404']  = "HTTP/1.0 404 Not Found";
$http_headers['503']  = "HTTP/1.0 503 Service Unavailable";

if (!$GLOBALS['failed']) {
//    trigger_error("Service-Health SUCCESS at ".date('M-d-y H:i:s')." on ".getActualHostname()." by ".getActualRemoteIP(), E_USER_WARNING); 
    die_with_header($http_headers['200']);
} else {
    foreach ($GLOBALS['failures'] as $item) {
        trigger_error("Service-Health FAILURE at ".date('M-d-y H:i:s')." on ".getActualHostname()." by ".getActualRemoteIP()." - $item", E_USER_WARNING);
    }
    die_with_header($http_headers['503']);
}
exit;

function displayTestResults($description, $testFunction) {
    $md5 = md5($description);
    echo '<div class="commit hg"><div class="author" id="'.$md5.'">...</div><p class="summary">'.$description.'</p></div>';
    if ($testFunction()) {
        echo "<script>$(\"#$md5\").html(\"<img src='yes.png' width=24px height=24px>\");</script>";
    } else {
        $GLOBALS['failed'] = TRUE;
        $GLOBALS['failures'][] = $description;
        echo "<script>$(\"#$md5\").html(\"<img src='no.png' width=24px height=24px>\");</script>";
    }
}


// Sends a header and a optional message to anyone who calls this script
function die_with_header($http_header, $message = "") {
    header($http_header);
    die();
    // if (empty($message))
    //     die($http_header);
    // else
    //     die($message);
}

function get_tmp_path() {
    $tmpfile = tempnam("dummy","");
    $path = dirname($tmpfile);
    unlink($tmpfile);
    return $path;
}

function getActualHostname() {
    if (empty($GLOBALS['ACTUAL_SERVER_HOSTNAME'])) {
        $GLOBALS['ACTUAL_SERVER_HOSTNAME'] = php_uname('n');  //This usually works...
        //if this fails, try hostname unix command (only works on unix)
        if (empty($GLOBALS['ACTUAL_SERVER_HOSTNAME'])) {
            $GLOBALS['ACTUAL_SERVER_HOSTNAME'] = @exec('/bin/hostname');
            //if all else fails, use DNS lookup (if we have to)
            if (empty($GLOBALS['ACTUAL_SERVER_HOSTNAME'])) {
                $GLOBALS['ACTUAL_SERVER_HOSTNAME'] = gethostbyaddr($_SERVER['SERVER_ADDR']);
                //if THAT failed, then I freakin' give up
                if (empty($GLOBALS['ACTUAL_SERVER_HOSTNAME']))
                    $GLOBALS['ACTUAL_SERVER_HOSTNAME'] = 'unknown';
            }
        }
    }
    return $GLOBALS['ACTUAL_SERVER_HOSTNAME'];
}

function curlRequest($url, $method = 'GET', $args = array(), $json = false, $accept_ssl = false, $timeout = 30) {
    if($method === 'GET' && !empty($args) && is_array($args)) {
        $parms = array();
        foreach($args as $key=>$val) {
            $parms[] = $arg[] = $key.'='.$val;
        }
        $url .= '?'.implode('&', $parms);
    }
    $h = curl_init($url);
    curl_setopt($h, CURLOPT_CUSTOMREQUEST, $method);
    curl_setopt($h, CURLOPT_HEADER, false);
    curl_setopt($h, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($h, CURLOPT_TIMEOUT, $timeout);
    if($method === 'POST' && $args) {
        curl_setopt($h, CURLOPT_POSTFIELDS, $args);
    }
    if ($accept_ssl) {
        curl_setopt($h, CURLOPT_SSL_VERIFYPEER, false);
    }
    $res = curl_exec($h);
    if (empty($res)) {
        $res = curl_error($h);
    }
    else if($json && ($decoded = json_decode($res, true))) {
        $res = $decoded;
    }
    return $res;
}

function getActualRemoteIP() {
	// Check if our forwarded for header is set and our remote IP is a local network / known load balancer IP
	if ( !empty($_SERVER['HTTP_X_FORWARDED_FOR']) ) {
	   
		// Grab our array of forwarded for addresses
		$HTTP_X_FORWARDED_FOR = explode(',',$_SERVER['HTTP_X_FORWARDED_FOR']);
	   
		// If we got them, return the first non-local ip
		if ( $HTTP_X_FORWARDED_FOR != FALSE ) {
			$remote_ip = getNonLocalIPFromArray($HTTP_X_FORWARDED_FOR);
			if (strlen($remote_ip)) {
				return $remote_ip;
			}
		}
	}
   
	// Otherwise, if we have a remote address, return that
	if (isset($_SERVER['REMOTE_ADDR'])) {
		return $_SERVER['REMOTE_ADDR'];
	} 
	$remote_ip = getNonLocalIPFromArray($HTTP_X_FORWARDED_FOR);
   
	// If all else fails, return localhost (likely run from a cli script)
	return "localhost";
}

function getNonLocalIPFromArray($ip_array) {
	// If we have no more records to try, exit
	if (count($ip_array) == 0)
		return false;

	// Get the first to last IP in the array that is not a proxy/loadbalnacer/private IP
	$actual_remote_ip = trim(array_pop($ip_array));

	//Check if it's a local
    if (filter_var($actual_remote_ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_NO_PRIV_RANGE)) {
    	// If it's a real IP and not a private ip range (192.168.x, 10.x, 172.16.x)
    	return $actual_remote_ip;
    }

	return getNonLocalIPFromArray($ip_array);
}