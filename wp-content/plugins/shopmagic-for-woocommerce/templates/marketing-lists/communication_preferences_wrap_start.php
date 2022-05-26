<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<!doctype html>
<html <?php language_attributes(); ?>>

<head>
	<meta charset="UTF-8" />
	<meta name="viewport" content="width=device-width,initial-scale=1" />
	<meta name="robots" content="noindex, nofollow"/>
	<meta http-equiv="X-UA-Compatible" content="ie=edge">
	<title></title>
	<style>
		body {
			background-color: #F7F7F7;
			padding: 1rem;
			text-align: center;
			margin: 0;
			font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
		}
		main {
			max-width: 480px;
			margin: auto;
			padding: 1.5rem;
			background-color: #ffffff;
			border: 2px solid #d9d9d9;
			border-radius: .5rem;
		}

		main > form p {
			text-align: left;
		}

		input[type="submit"] {
			border: 1px solid #2D95F7;
			border-radius: .25rem;
			color: #2D95F7;
			padding: 1ex 2ex;
			background-color: #ffffff;
			cursor: pointer;
		}

		input[type="submit"]:is(:hover,:focus) {
			color: #ffffff;
			background-color: #2D95F7;
		}

		.shopmagic-message p {
			padding: 2ex;
			text-align: left;
			color: white;
		}

		.shopmagic-message .success {
			background-color: #0f834d;
		}

		.shopmagic-message .error {
			background-color: #b62529;
		}
	</style>
</head>
<body>
<h1><?php esc_html_e( 'Communication preferences', 'shopmagic-for-woocommerce' ); ?></h1>
<main>
