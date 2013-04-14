<?php 

function isJson($string) {
 json_decode($string);
 return (json_last_error() == JSON_ERROR_NONE);
}

function send_curl($url, $method = 'GET', $post_data = false, $username = false, $password = false){
 
    // is cURL installed yet?
    if (!function_exists('curl_init')){
        die('Sorry cURL is not installed!');
    }
    // OK cool - then let's create a new cURL resource handle
    $ch = curl_init();
    // Now set some options (most are optional)
    if($post_data){
	    //post data string
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
		//set HTTP header
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(                                                                          
		    'Content-Type: application/json',                                                                                
		    'Content-Length: ' . strlen($post_data))                                                                       
		); 
    }
    // Include header in result? (0 = yes, 1 = no)
    curl_setopt($ch, CURLOPT_HEADER, 0);
    // POST or GET?
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    // Set URL to download
    curl_setopt($ch, CURLOPT_URL, $url);
    // Set a referer
    curl_setopt($ch, CURLOPT_REFERER, $_SERVER['PHP_SELF']);
    // User agent
    curl_setopt($ch, CURLOPT_USERAGENT, "MozillaXYZ/1.0");
    if ($username && $password) {
	    // Set name and password
	    curl_setopt($ch, CURLOPT_USERPWD, $username . ":" . $password);
	}
    // Should cURL return or print out the data? (true = return, false = print)
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    // Timeout in seconds
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    // Download the given URL, and return output
    $output = curl_exec($ch);
    // Close the cURL resource, and free system resources
    curl_close($ch);
 
    return $output;
}

session_start();
$response = false;

if (isset($_GET) && !empty($_GET)) {
	if(isset($_GET['apibase'])){
		//strip the right slash and set's the URL to the session
		$_SESSION['apibase'] = rtrim($_GET['apibase'],"/");
	}
}

if (isset($_POST) && !empty($_POST)) {

	// stop yelling at me
	$post = $_POST;
	$error = array();
	$data = false;

	// Now for some validation!

	// query data is an array within the post array
	if(isset($post['query']) && !empty($post['query']) && is_array($post['query'])) {
		$data = json_encode($post['query']);
	}

	// validate method
	if(isset($post['method']) && !empty($post['method']) && $post['method'] == ('POST' OR 'GET')) {
		$method = $post['method'];
	} elseif($data) {
		$method = 'POST';
	} else {
		$method = 'GET';
	}

	// validate API baseUrl
	if(isset($_SESSION['apibase']) && !empty($_SESSION['apibase'])) {
		//Lets see if the api endpoint is valid
		if(!filter_var($_SESSION['apibase'], FILTER_VALIDATE_URL)) {
			$error['baseUrl'] = 'Please double check the URL, should look like this: http://example.com';
		} else {
			$url = $_SESSION['apibase'];
		}
	} else {
		//Whoops someone didn't set the base API URL
		$error['baseUrl'] = 'You\'ll need to provide a base API url';
	}

	// validate and build the API url
	if(isset($post['endpoint']) && !empty($post['endpoint']) && isset($url)) {
		// we have a proper endpoint! Lets strip the slash and mash it together with the url
		$query = trim($url) . '/' . trim(ltrim($post['endpoint'],"/"));
	} else {
		$error['endpoint'] = 'Missing endpoint for our URL';
	}

	// If we are GETing reports
	if(isset($post['name']) && $post['name'] == 'query_reports'){
		// If the report_id is set and not empty, tack that on to the end of the query
		if(isset($post['report_id']) && !empty($post['report_id'])){
			$query = $query . '/' . trim($post['report_id']);
		}
	}

	// if there are no major errors, lets curl some API!
	if(empty($error)){
		$response = send_curl($query, $method, $data);	
	}

}

?>

<!DOCTYPE html>
<!--[if IE 8]> <html class="no-js lt-ie9" lang="en" > <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js" lang="en" > <!--<![endif]-->

<head>
	<meta charset="utf-8" />
  <meta name="viewport" content="width=device-width" />
  <title></title>

  <link rel="stylesheet" href="css/normalize.css" />
  <link rel="stylesheet" href="css/foundation.min.css" />
  <link rel="stylesheet" href="site.css" />
  
  <script src="js/vendor/custom.modernizr.js"></script>

</head>
<body>

	<div class="row">
		<div class="large-12 columns">
			<h2>Taarifa API Test</h2>
		</div>
	</div>
	<?php if (!empty($error)){ ?>
			<div class="row">
				<div class="large-12 column">
	<?php foreach ($error as $name => $value) {
	?>
					<div class="alert-box alert"><strong><?php echo $name ?>:</strong> <?php echo $value ?></div>
	<?php
		} ?>
				</div>
			</div>
	<?php } ?>

	<div class="row">
		<form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="GET" class="large-12 column custom">
			<fieldset>
				<legend>API Base URL</legend>
				<div class="row collapse">	
					<div class="large-10 small-8 column">
						<input id="baseUrl" type="url" name="apibase" value="<?php if(isset($_SESSION['apibase'])){ echo $_SESSION['apibase']; } ?>" required>
					</div>
					<div class="large-2 small-4 column">
						<input type="submit" value="Set Url" class="button postfix">
					</div>
				</div>
			</fieldset>
		</form>
	</div>
	<?php if($response): ?>
	<div id="response" class="row">
		<div class="large-12 column">
			<strong>Query:</strong> <?php echo $query; ?>
			<?php if($data): ?> <br>
			<strong>Data Send:</strong> <?php echo $data ?>
			<?php endif; ?>
		</div>
		<fieldset class="large-12 column">
			<legend>API Response</legend>
			<?php 
			if(isJson($response)){
				print('<pre><code>' . $response . '</code></pre>');
			} else {
				print($response); 
			}
			?>
		</fieldset>
	</div>
	<?php endif; ?>
	<div class="row">
		<div class="large-6 column">
			<form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="POST">
				<fieldset>
					<!-- be sure to set the endpoint and method, end point should have a leading slash
					but we'll double check that in the code -->
					<input type="hidden" name="name" value="query_reports">
					<input type="hidden" name="endpoint" value="/reports">
					<input type="hidden" name="method" value="GET">
			
					<legend>Look up report <small class="baseUrlDisplay"><?php if(isset($_SESSION['apibase'])){ echo $_SESSION['apibase']; } ?></small><small class="uriDisplay"></small></legend>
					<input type="number" name="report_id">
					<input type="submit" class="button">
				</fieldset>
			</form>
			<form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="POST">
				<fieldset>
					<!-- be sure to set the endpoint and method, end point should have a leading slash
					but we'll double check that in the code -->
					<input type="hidden" name="name" value="query_reports">
					<input type="hidden" name="endpoint" value="/services">
					<input type="hidden" name="method" value="GET">
			
					<legend>List Services <small class="baseUrlDisplay"><?php if(isset($_SESSION['apibase'])){ echo $_SESSION['apibase']; } ?></small><small class="uriDisplay"></small></legend>
					<input type="submit" class="button">
				</fieldset>
			</form>
		</div>
		<form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="POST" class="large-6 columns">
			<fieldset>
				<!-- be sure to set the endpoint and method, end point should have a leading slash
				but we'll double check that in the code -->
				<input type="hidden" name="name" value="post_reports">
				<input type="hidden" name="endpoint" value="/reports">
				<input type="hidden" name="method" value="POST">

				<legend>Add report <small class="baseUrlDisplay"><?php if(isset($_SESSION['apibase'])){ echo $_SESSION['apibase']; } ?></small><small class="uriDisplay"></small></legend>
				<label for="query[title]">Title</label>
				<input type="text" name="query[title]" value="<?php if(isset($post['query[title]'])){ echo $post['query[title]']; } ?>" required>
				<div class="row">
					<div class="large-6 columns">
						<label for="query[latitude]">Latitude</label>
						<input type="text" name="query[latitude]" value="<?php if(isset($post['query[latitude]'])){ echo $post['query[latitude]']; } ?>" required>
					</div>
					<div class="large-6 columns">
						<label for="query[longitude]">Longitude</label>
						<input type="text" name="query[longitude]" value="<?php if(isset($post['query[longitude]'])){ echo $post['query[longitude]']; } ?>" required>
					</div>
				</div>
				<label for="query[report_id]">Report ID</label>
				<input type="number" name="query[report_id]" value="<?php if(isset($post['query[report_id]'])){ echo $post['query[report_id]']; } ?>" required>
				<input type="submit" class="button">
			</fieldset>
		</form>
	</div>

  <script>
  document.write('<script src=' +
  ('__proto__' in {} ? 'js/vendor/zepto' : 'js/vendor/jquery') +
  '.js><\/script>')
  </script>
  
  <script src="js/foundation.min.js"></script>
  <script src="js/site.js"></script>
  <!--
  
  <script src="js/foundation/foundation.js"></script>
  
  <script src="js/foundation/foundation.dropdown.js"></script>
  
  <script src="js/foundation/foundation.placeholder.js"></script>
  
  <script src="js/foundation/foundation.forms.js"></script>
  
  <script src="js/foundation/foundation.reveal.js"></script>
  
  <script src="js/foundation/foundation.tooltips.js"></script>
  
  <script src="js/foundation/foundation.clearing.js"></script>
  
  <script src="js/foundation/foundation.section.js"></script>
  
  <script src="js/foundation/foundation.topbar.js"></script>
  
  -->
  
  <script>
    $(document).foundation();

var baseUrl = '';
var queryString = {};

setBaseUrl('#baseUrl','.baseUrlDisplay');

function setBaseUrl (watch,target) {
	$(watch).keyup(function() {
		$(target).text( $(watch).val() );
		baseUrl = $(watch).val();
	});

}

$('form').each(function(){
	$('.uriDisplay',this).text( $('input[name=endpoint]',this).val() + ' ' + $('input[name=method]',this).val() );
})

  </script>
</body>
</html>
