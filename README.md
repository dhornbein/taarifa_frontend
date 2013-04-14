Taarifa Frontend Example Test Trial System
====

This PHP script is meant to provide an example of both a hastily written PHP script and a web app that uses the Taarifa API to GET and POST data.

This tool was written by [Drew Hornbein](//github.com/dhornbein) at the [h4d2](//h4d2.eu) Hack-a-Thon. April 14th, Two Thousand Thirteen.

## Using the service

You can find a demo of this service here (actually there is nothing "here" yet, someone will surly make a pull request with the correct link)

Once in the service, you'll need to set the Base URL. This base URL is saved in the session data so you don't have to type it in over and over.

## A look at the code

First things first, you must set

If you're brave enough to dig into this code it probably means that you want to add some functionality to updates of the Taarifa API.

Here's the form that POSTs to the API:

```
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
```

Isn't that nice? Let's start with the hidden inputs.

```
<input type="hidden" name="name" value="post_reports">
<input type="hidden" name="endpoint" value="/reports">
<input type="hidden" name="method" value="POST">
```
We've got a `name` so you can manipulate this specific form. Next is the API `endpoint` which is the bit after our URL (http://example.com**/api/endpoint**). Finally is the `method` this defines, wait for it, the method it only accepts 'GET' and 'POST'. 

If you're building a new form for the api you'll need *at least* these three hidden fields along with a submit button.

### POSTing data

If you want to POST data to the API it's as easy as something fairly easy. Here's an example input:

`<input type="number" name="query[report_id]" value="<?php if(isset($post['query[report_id]'])){ echo $post['query[report_id]']; } ?>" required>`

Turn your attention to `name="query[report_id]"`, all data that you want to pass via JSON to the API will need to be within the query array. So when this form is submitted it will pass the following JSON to the API `{"report_id":"what ever data you entered"}`. Magical!

### The small bits

Check out the legend! 

```
<legend>Add report <small class="baseUrlDisplay"><?php if(isset($_SESSION['apibase'])){ echo $_SESSION['apibase']; } ?></small><small class="uriDisplay"></small></legend>
```

There is some Javascript that automagically drops in the `baseURL` as you type it into the input above. The Javascript also drops the endpoint and method into `.uriDisplay` element. If you make a new form, just add this code and it will just work.

That's it.

If you have any questions feel free to drop me an e-mail hello /at/ dhornbein.com

and remember: f*ck the banks!