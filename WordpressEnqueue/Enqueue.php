<?php

namespace WordpressEnqueue;

abstract class Enqueue {

	private $version;
	private $dir;
	private $url;

	protected function __construct( $dir, $url, $version ) {
		$this->set_dir( $dir );
		$this->set_url( $url );
		$this->set_version( $version );
	}

	/**
	 * @param $args array
	 */
	protected function _register_style( $args ) {
		$parsed_args = $this->_parse_style_args( $args );

		extract( $parsed_args );

		wp_register_style( $handle, $src, $deps, $version, $media );

		$this->_make_inline_style( $inline, $handle, $relativepath );
	}

	/**
	 * @param $args array
	 */
	protected function _register_script( $args ) {
		$parsed_args = $this->_parse_script_args( $args );

		extract( $parsed_args );

		wp_register_script( $handle, $src, $deps, $version, $in_footer );

		$this->_make_inline_script( $inline, $handle, $relativepath );
	}

	/**
	 * @param $args array
	 */
	protected function _enqueue_style( $args ) {
		$parsed_args = $this->_parse_style_args( $args );

		extract( $parsed_args );

		wp_enqueue_style( $handle, $src, $deps, $version, $media );

		$this->_make_inline_style( $inline, $handle, $relativepath );
	}

	/**
	 * @param $args array
	 */
	protected function _enqueue_script( $args ) {
		$parsed_args = $this->_parse_script_args( $args );

		extract( $parsed_args );

		wp_enqueue_script( $handle, $src, $deps, $version, $in_footer );

		$this->_make_inline_script( $inline, $handle, $relativepath );
	}

	protected function _print_styles_inline( $args ) {
		$parsed_args = $this->_parse_style_args( $args );

		extract( $parsed_args );

		if ( ! $this->_file_exists( $relative_path ) ) {
			return;
		}

		?>
        <style id="<?php echo esc_attr( $handle ) . '-css'; ?>"><?php include $this->_get_filename( $relative_path ); ?></style>
		<?php
	}

	protected function _print_scripts_inline( $args ) {
		$parsed_args = $this->_parse_script_args( $args );

		extract( $parsed_args );

		if ( ! $this->_file_exists( $relative_path ) ) {
			return;
		}

		?>
        <script id="<?php echo esc_attr( $handle ) . '-js'; ?>"><?php include $this->_get_filename( $relative_path ); ?></script>
		<?php
	}

	protected function _make_inline_style( $inline, $handle, $relative_path ) {
		if ( ! empty( $inline ) ) {
			return;
		}

		if ( ! $this->_file_exists( $relative_path ) ) {
			return;
		}

		$filename = $this->_get_filename( $relative_path );

		ob_start();
		include $relative_path;
		$css = ob_get_clean();

		wp_add_inline_style( $handle, $css );

		$style_handle = $handle;

		add_filter( 'style_loader_tag', function ( $tag, $handle ) use ( $style_handle ) {
			if ( $style_handle == $handle ) {
				$tag = '';
			}

			return $tag;
		}, 10, 2 );
	}

	protected function _make_inline_script( $inline, $handle, $relative_path ) {
		if ( ! empty( $inline ) ) {
			return;
		}

		if ( ! $this->_file_exists( $relative_path ) ) {
			return;
		}

		$filename = $this->_get_filename( $relative_path );

		ob_start();
		include $relative_path;
		$css = ob_get_clean();

		wp_add_inline_script( $handle, $css );

		add_filter( 'wp_script_attributes', function ( $attributes ) use ( $handle ) {
			if ( ! empty( $attributes['id'] ) && "{$handle}-js" == $attributes['id'] ) {
				$attributes['src'] = '';
			}

			return $attributes;
		} );
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

		$parsed_args['relative_path'] = $relative_path;

		$parsed_args['inline'] = ! empty( $args['inline'] ) ? $args['inline'] : false;

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

	/**
	 * @param $relative_path
	 *
	 * @return string
	 */
	protected function _get_filename( $relative_path ) {
		return $this->dir . $relative_path;
	}

	/**
	 * @param $relative_path
	 *
	 * @return bool
	 */
	protected function _file_exists( $relative_path ) {
		$filename = $this->_get_filename( $relative_path );

		return file_exists( $filename );
	}

	/**
	 * @return mixed
	 */
	public function get_version() {
		return $this->version;
	}

	/**
	 * @param mixed $version
	 */
	public function set_version( $version ) {
		$this->version = $version;
	}

	/**
	 * @return mixed
	 */
	public function get_dir() {
		return $this->dir;
	}

	/**
	 * @param mixed $dir
	 */
	public function set_dir( $dir ) {
		$this->dir = $dir;
	}

	/**
	 * @return mixed
	 */
	public function get_url() {
		return $this->url;
	}

	/**
	 * @param mixed $url
	 */
	public function set_url( $url ) {
		$this->url = $url;
	}


}