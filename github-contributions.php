<?php
/*
Plugin Name: Github Contributions
Description: A widget for displaying github contributions
Version: 0.1
Author: akirk
Author URI: https://alexander.kirk.at
License: GPL2
*/

class Github_Contributions extends WP_Widget {
	private $defaults = array(
		'username' => false,
		'sort' => 'date',
		'limit' => 100,
	);
	public function __construct() {
		parent::__construct( 'github-contributions-widget', 'Github Contributions', array(
			'description' => 'Displays your Github contrbutions.',
		) );
	}

	public static function fetch_contributions( $username, $sort = 'date', $limit = 100 ) {
		$key = 'github_contributions' . $username . $sort . '_' . $limit;
		$contributions = get_transient( $key );

		if ( $contributions === false ) {
			$response = wp_remote_get( 'https://api.github.com/search/issues?q=type%3apr+state%3aclosed+author%3a' . urlencode( $username ) . '&per_page=100&page=1' );

			if ( is_wp_error( $response ) ) {
				$contributions = get_option( $key );
				if ( ! $contributions ) {
					return $response;
				}
			} else {
				$contributions = json_decode( wp_remote_retrieve_body( $response ) );
				set_transient( $key, $contributions, DAY_IN_SECONDS );
				update_option( $key, $contributions );
			}
		}

		return $contributions;
	}

	public static function render_contributions( $contributions, $instance ) {
		$repos = array();
		foreach ( $contributions->items as $item ) {
			if ( ! isset( $repos[ $item->repository_url ] ) ) {
				$repos[ $item->repository_url ] = 0;
			}
			$repos[ $item->repository_url ] += 1;
		}

		if ( isset( $instance['sort'] ) && $instance['sort'] === 'count' ) {
			arsort( $repos );
		}

		$html = '<ul>';
		foreach ( $repos as $url => $count ) {
			if ( is_numeric( $instance['limit'] ) ) {
				if ( $instance['limit'] <= 0 ) {
					break;
				}
				$instance['limit'] -= 1;
			}

			$html .= '<li><a href="' . esc_url( str_replace( 'https://api.github.com/repos/', 'https://github.com/', $url ) . '/commits?author=' . urlencode( $instance['username'] ) ) . '">' . esc_html( trim( str_replace( 'https://api.github.com/repos/', '', $url ), '/' ) ) . '</a>';
			$html .= '<br/>' . sprintf( _n( '%d contribution', '%d contributions', esc_html( $count ) ), $count );
			$html .= '</li>';
		}
		$html .= '</ul>';
		return $html;
	}

	public function widget( $args, $instance ) {
		$title = apply_filters( 'widget_title', $instance['title'] );
		echo $args['before_widget'];
		if ( ! empty( $title ) ) {
			echo $args['before_title'] . $title . $args['after_title'];
		}

		if ( $instance['username'] ) {
			$contributions = self::fetch_contributions( $instance['username'], $instance['sort'], $instance['limit'] );

			echo wp_kses_post( self::render_contributions( $contributions, $instance ) );
		}

		echo $args['after_widget'];
	}

	public function form( $instance ) {
		$instance = wp_parse_args( $instance, $this->defaults );

		?><p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>">Title:</label>
			<input class="title widefat" id="<?php echo $this->get_field_id( 'title' ); ?>"
				name="<?php echo $this->get_field_name( 'title' ); ?>" type="text"
				value="<?php echo esc_attr( $instance['title'] ); ?>" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'username' ); ?>">Username:</label>
			<input class="username" id="<?php echo $this->get_field_id( 'username' ); ?>"
				name="<?php echo $this->get_field_name( 'username' ); ?>" type="text"
				value="<?php echo esc_attr( $instance['username'] ); ?>" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'limit' ); ?>">Limit:</label>
			<input class="limit" id="<?php echo $this->get_field_id( 'limit' ); ?>"
				name="<?php echo $this->get_field_name( 'limit' ); ?>" type="number" min="1" max="1000"
				value="<?php echo esc_attr( $instance['limit'] ); ?>" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'sort' ); ?>">Sort:</label>
			<select class="sort" id="<?php echo $this->get_field_id( 'sort' ); ?>" name="<?php echo $this->get_field_name( 'sort' ); ?>"">
				<option value="date"<?php if ( $instance['type'] === 'date' ) echo ' selected="selected"'; ?>>by last contribution</option>
				<option value="count"<?php if ( $instance['type'] === 'count' ) echo ' selected="selected"'; ?>>by amount of contributions</option>
			</select>
		</p><?php
	}

	public function update( $new_instance, $old_instance ) {	
		$instance = $this->defaults;

		if ( ! empty( $new_instance['title'] ) ) {
			$instance['title'] = strip_tags( $new_instance['title'] );
		}

		if ( ! empty( $new_instance['username'] ) ) {
			$instance['username'] = strip_tags( $new_instance['username'] );
		}

		if ( ! empty( $new_instance['sort'] ) ) {
			$instance['sort'] = strip_tags( $new_instance['sort'] );
		}

		if ( ! empty( $new_instance['limit'] ) && is_numeric( $new_instance['limit'] ) ) {
			$instance['limit'] = intval( $new_instance['limit'] );
		}

		return $instance;	
	}

	public static function register() {
		register_widget( __CLASS__ );
	}
}

add_action( 'widgets_init', array( 'Github_Contributions', 'register' ) );

add_action( 'init', function() {
	register_block_type_from_metadata(
		__DIR__,
		array(
			'render_callback' => function( $attributes ) {
				if ( ! isset( $attributes['username'] ) ) {
					// return '<p>No Github username specified</p>';
					$attributes['username'] = 'demo';
				}

				if ( 'demo' === $attributes['username'] ) {
					$contributions = (object) array(
						'items' => array(
							(object) array(
								'repository_url' => 'https://github.com/demo/demo',
							),
						),
					);
				} else {
					$contributions = Github_Contributions::fetch_contributions( $attributes['username'] );
				}

				if ( is_wp_error( $contributions ) ) {
					return '<p>Error: ' . $contributions->get_error_message() . '</p>';
				}
				return Github_Contributions::render_contributions( $contributions, $attributes );
			},
		)
	);
} );
