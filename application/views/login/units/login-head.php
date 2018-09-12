<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<!DOCTYPE html>
<html lang="ru" prefix="og: http://ogp.me/ns#">
<head>
<meta charset="UTF-8">
<title><?= $PAGE_TITLE ?></title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="description" content="<?= $PAGE_DESCRIPTION ?>">
<meta name="keywords" content="<?= $PAGE_KEYWORDS ?>">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.5.0/css/font-awesome.min.css">
<style>
	*, *:after, *:before {box-sizing: border-box; font-size:inherit; line-height:1.4em}
	h1 {font-size:24px; margin:8px; padding:0; line-height:1.4em; font-weight:700; text-align:center}
	label {cursor: pointer;display: inline-block}
	label.label-checkbox {padding-left: 3px}
	.login-page {background:#fff}
	.login-logo {margin: 0 auto;display: block;max-width: 100%;}
	.login-box {width: 100%;max-width: 560px; margin:7% auto;}
	.login-body {background:#fff; padding:15px;border: 1px solid #ccc}
	.login-body input[type="text"], .login-body input[type="password"] {width:100%; font-size:16px; border:1px solid #d2d6de; padding:5px 8px; margin:0;height: 38px;}
	.form-group {margin:24px 0; position:relative}
	.form-group.button {text-align:right}
	.form-control-feedback {position: absolute;top: 0;right:0;z-index: 2;display: block;width: 34px;height: 34px;line-height: 34px;text-align: center;pointer-events: none;}
	/*.glyphicon-user:before {content: "\e008";}*/
	.btn {display: inline-block;padding: 6px 12px;margin-bottom: 0;font-size: 14px;font-weight: normal;line-height: 1.42857143;text-align: center;white-space: nowrap;vertical-align: middle;-ms-touch-action: manipulation;touch-action: manipulation;cursor: pointer;-webkit-user-select: none;-moz-user-select: none;-ms-user-select: none;user-select: none;background-image: none;border: 1px solid transparent;border-radius: 4px;}
	.btn-primary:hover {color: #fff;background-color: #286090;border-color: #204d74;}
	.btn-primary {color: #fff;background-color: #337ab7;border-color: #2e6da4;}
	.error-message {color: #b20000;text-align: center;}
	.form-group.submit {text-align:center}

	ul.form-links  {padding: 0;margin: 0;list-style-type: none;padding-left: 24px}
	ul.form-links li {text-align: left}
	ul.form-links a {color:#888888; text-decoration:none;font-size: 18px;}
	ul.form-links a:hover {text-decoration: underline}

	.btn.submit {width:90%; margin-bottom:15px}
	.login-body input[type="checkbox"] {display: none}
	.login-body input[type="checkbox"] + span {font-size: 16px;display: block}
	.login-body input[type="checkbox"] + span:before {display: inline-block;vertical-align: middle;width: 12px;height: 12px;content: '';outline: 1px solid #ccc;outline-offset: 2px;margin-right: 10px}
	.login-body input[type="checkbox"]:checked + span:before {background-color: #337ab7}
    .register-user-agreement {font-size: 14px;font-style: italic;color:#b20}
	<?= app_get_option('login_custom_css', 'site', '') ?>
</style>

</head>
