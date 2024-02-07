<?php

defined( 'ABSPATH' ) || exit;

/**
 * Autoload the OM4\WooCommerceMixCheck namespaced classes.
 * Helps keep code simple and memory consumption down.
 * Idea from: http://container.thephpleague.com/3.x/#going-solo.
 */
spl_autoload_register(
	function ( $class ) {
		$prefix   = 'OM4\\WooCommerceMixCheck\\';
		$base_dir = __DIR__ . '/src/';
		$len      = strlen( $prefix );
		if ( strncmp( $prefix, $class, $len ) !== 0 ) {
			// No, move to the next registered autoloader.
			return;
		}
		$relative_class = substr( $class, $len );
		$file           = $base_dir . str_replace( '\\', '/', $relative_class ) . '.php';
		if ( file_exists( $file ) ) {
			require_once $file;
		}
	}
);

/**
 * Autoload the OM4\MixCheck (legacy 1.9.x) namespaced classes.
 * Helps keep code simple and memory consumption down.
 * Idea from: http://container.thephpleague.com/3.x/#going-solo.
 */
spl_autoload_register(
	function ( $class ) {
		$prefix   = 'OM4\\MixCheck\\';
		$base_dir = __DIR__ . '/legacy/';
		$len      = strlen( $prefix );
		if ( strncmp( $prefix, $class, $len ) !== 0 ) {
			// No, move to the next registered autoloader.
			return;
		}
		$relative_class = substr( $class, $len );
		$file           = $base_dir . str_replace( '\\', '/', $relative_class ) . '.php';
		if ( file_exists( $file ) ) {
			require_once $file;
		}
	}
);

/**
 * Always load functions file.
 * Part of the legacy (1.9.x) functionality
 */
require_once __DIR__ . '/functions-legacy.php';
