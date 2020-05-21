<?php

/**
 * @wordpress-plugin
 * Plugin Name:       Text Domain Cache
 * Plugin URI:        https://github.com/Kntnt/textdomain-cache
 * Description:       Caches MO-files for faster load_textdomain(). Install an object cache for best performance.
 * Version:           1.0.0
 * Author:            Per Soderlind
 * Author URI:        https://soderlind.no/a-faster-load_textdomain-for-wordpress/
 * License:           GPL-3.0+
 * License URI:       http://www.gnu.org/licenses/gpl-3.0.txt
 *
 * Based on https://core.trac.wordpress.org/ticket/32052.
 */

add_filter( 'override_load_textdomain', function ( $retval, $domain, $mofile ) {

	global $l10n;

	if ( ! is_readable( $mofile ) ) {
		return false;
	}

	$data = get_transient( md5( $mofile ) );
	$mtime = filemtime( $mofile );

	$mo = new MO();
	if ( ! $data || ! isset( $data['mtime'] ) || $mtime > $data['mtime'] ) {
		if ( ! $mo->import_from_file( $mofile ) ) {
			return false;
		}
		$data = [
			'mtime' => $mtime,
			'entries' => $mo->entries,
			'headers' => $mo->headers,
		];
		set_transient( md5( $mofile ), $data );
	}
	else {
		$mo->entries = $data['entries'];
		$mo->headers = $data['headers'];
	}

	if ( isset( $l10n[ $domain ] ) ) {
		$mo->merge_with( $l10n[ $domain ] );
	}

	$l10n[ $domain ] = &$mo;

	return true;

}, 1, 3 );
