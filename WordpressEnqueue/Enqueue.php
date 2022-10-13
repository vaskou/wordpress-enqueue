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
	 * @param $args array
	 */
	protected function _register_style( $args ) {
		$parsed_args = $this->_parse_style_args( $args );

		extract( $parsed_args );

		wp_register_style( $handle, $src, $deps, $version, $media );
	}

	/**
	 * @param $args array
	 */
	protected function _register_script( $args ) {
		$parsed_args = $this->_parse_script_args( $args );

		extract( $parsed_args );

		wp_register_script( $handle, $src, $deps, $version, $in_footer );
	}

	/**
	 * @param $args array
	 */
	protected function _enqueue_style( $args ) {
		$parsed_args = $this->_parse_style_args( $args );

		extract( $parsed_args );

		wp_enqueue_style( $handle, $src, $deps, $version, $media );
	}

	/**
	 * @param $args array
	 */
	protected function _enqueue_script( $args ) {
		$parsed_args = $this->_parse_script_args( $args );

		extract( $parsed_args );

		wp_enqueue_script( $handle, $src, $deps, $version, $in_footer );
	}

	protected function _parse_style_args( $args ) {
		$parsed_args = $this->_parse_args( $args );

		$parsed_args['media'] = ! empty( $args['media'] ) ? $args['media'] : 'all';

		return $parsed_args;
	}

	protected function _parse_script_args( $args ) {
		$parsed_args = $this->_parse_args( $args );

		$parsed_args['in_footer'] = ! empty( $args['in_footer'] ) ? $args['in_footer'] : true;

		return $parsed_args;
	}

	protected function _parse_args( $args ) {
		$relative_path = ! empty( $args['relative_path'] ) ? $args['relative_path'] : '';

		$url = ! empty( $relative_path ) ? $this->url . $relative_path : '';

		$url = ! empty( $args['url'] ) ? $args['url'] : $url;

		$parsed_args['handle'] = ! empty( $args['handle'] ) ? $args['handle'] : '';

		$parsed_args['src'] = $url;

		$parsed_args['deps'] = ! empty( $args['deps'] ) ? $args['deps'] : [];

		$parsed_args['version'] = $this->_get_file_version( $relative_path );

		return $parsed_args;
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