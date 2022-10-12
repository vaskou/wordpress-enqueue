<?php

namespace WordpressEnqueue;

abstract class Enqueue {

	private $version;
	private $dir;
	private $url;

	protected function __construct( $dir, $url, $version ) {
		$this->dir     = $dir;
		$this->url     = $url;
		$this->version = $version;
	}

	/**
	 * @param $handle string
	 * @param $relative_path string
	 * @param $deps string[]
	 */
	protected function _register_style( $handle, $relative_path, $deps = array() ) {
		$version = $this->_get_file_version( $relative_path );
		wp_register_style( $handle, $this->url . $relative_path, $deps, $version );
	}

	/**
	 * @param $handle string
	 * @param $relative_path string
	 * @param $deps string[]
	 */
	protected function _register_script( $handle, $relative_path, $deps = array() ) {
		$version = $this->_get_file_version( $relative_path );
		wp_register_script( $handle, $this->url . $relative_path, $deps, $version, true );
	}

	/**
	 * @param $args array
	 */
	protected function _enqueue_style( $args ) {
		$parsed_args = $this->_parse_style_args( $args );

		extract( $parsed_args );

		wp_enqueue_style( $handle, $src, $deps, $version );
	}

	protected function _parse_style_args( $args ) {
		$relative_path = ! empty( $args['relative_path'] ) ? $args['relative_path'] : '';

		$parsed_args['handle'] = ! empty( $args['handle'] ) ? $args['handle'] : '';

		$parsed_args['src'] = ! empty( $relative_path ) ? $this->url . $relative_path : '';

		$parsed_args['deps'] = ! empty( $args['deps'] ) ? $args['deps'] : [];

		$parsed_args['version'] = $this->_get_file_version( $parsed_args['relative_path'] );

		return $parsed_args;
	}

	/**
	 * @param $handle string
	 * @param $relative_path string
	 * @param $deps string[]
	 */
	protected function _enqueue_script( $handle, $relative_path = '', $deps = array() ) {
		$version = $this->_get_file_version( $relative_path );

		$src = ! empty( $relative_path ) ? $this->url . $relative_path : '';

		wp_enqueue_script( $handle, $src, $deps, $version, true );
	}

	/**
	 * @return string
	 */
	protected function _get_assets_prefix() {
		return defined( 'WP_DEBUG' ) && WP_DEBUG ? '' : '.min';
	}

	/**
	 * @param $filename
	 *
	 * @return string
	 */
	protected function _get_file_version( $filename ) {

		$filename = $this->dir . $filename;

		$filetime = file_exists( $filename ) ? filemtime( $filename ) : '';

		return $this->version . ( ! empty( $filetime ) ? '-' . $filetime : '' );
	}
}