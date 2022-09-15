<?php 

if ( ! defined( 'ABSPATH' ) ) exit;

class Wbe_Posts_Filter extends \Bricks\Element {

	public $category     = 'widdin-wbe';
	public $name         = 'wbe-posts-filter';
	public $icon         = 'ti-layout-list-thumb';
	public $css_selector = '.wbe-posts-filter-wrapper';
	public $scripts      = ['sendAJAX'];


	public function get_label() {
		return esc_html__( 'Posts Filter', 'bricks' );
	}

	public function __construct( $element = null ) {
		parent::__construct( $element );

		add_action('wp_ajax_filterposts', [$this, 'filter_posts']); 
		add_action('wp_ajax_nopriv_filterposts', [$this, 'filter_posts']);
	}

	public function set_control_groups() {
		$this->control_groups['settings'] = [
			'title' => esc_html__( 'Settings', 'bricks' ),
			'tab' => 'content',
		];

		$this->control_groups['filterActive'] = [
			'title' => esc_html__( 'Filter Active', 'bricks' ),
			'tab' => 'content',
		];

		$this->control_groups['filter'] = [
			'title' => esc_html__( 'Filter', 'bricks' ),
			'tab' => 'content',
		];

		$this->control_groups['categories'] = [
			'title' => esc_html__( 'Categories', 'bricks' ),
			'tab' => 'content',
		];
	}

	public function set_controls() {
		$this->controls['infiniteScroll'] = [
			'tab' => 'content',
			'group' => 'settings',
			'label' => esc_html__( 'Infinite Scroll', 'bricks' ),
			'description' => esc_html__( 'Loads content continuously as the user scrolls down the page.', 'bricks' ),
			'type' => 'checkbox',
			'inline' => true,
			'small' => true,
			'default' => false,
		];

		$this->controls['postsPerPage'] = [
			'tab' => 'content',
			'group' => 'settings',
			'label' => esc_html__( 'Posts per page', 'bricks' ),
			'description' => esc_html__( 'number of post to show per page.', 'bricks' ),
			'type' => 'number',
			'min' => 0,
			'inline' => true,
			'default' => 9,
		];

		$this->controls['post_type'] = [
			'tab'         => 'content',
			'group'       => 'settings',
			'label'       => esc_html__( 'Post type', 'bricks' ),
			'type'        => 'select',
			'options'     => $this->get_registered_post_types(),
			'clearable'   => true,
			'inline'      => true,
			'default'     => 'post',
		];

		$this->controls['filterActiveTypography'] = [
			'tab' => 'content',
			'group' => 'filterActive',
			'label' => esc_html__( 'Typography', 'bricks' ),
			'type' => 'typography',
			'css' => [
				[
					'property' => 'typography',
					'selector' => '.wbe-radio-toolbar input[type="radio"]:checked+label',
				],
			],
			'inline' => true,
		];

		$this->controls['filterActiveBackgroundColor'] = [
			'tab' => 'content',
			'group' => 'filterActive',
			'label' => esc_html__( 'Background Color', 'bricks' ),
			'type' => 'color',
			'inline' => true,
			'css' => [
				[
				  'property' => 'background-color',
				  'selector' => '.wbe-radio-toolbar input[type="radio"]:checked+label',
				]
			],
		];

		$this->controls['filterActiveBorder'] = [
		  'tab' => 'content',
		  'group' => 'filterActive',
		  'label' => esc_html__( 'Border', 'bricks' ),
		  'type' => 'border',
		  'css' => [
			[
			  'property' => 'border',
			  'selector' => '.wbe-radio-toolbar input[type="radio"]:checked+label',
			],
		  ],
		  'inline' => true,
		  'small' => true,
		];

		$this->controls['filterTypography'] = [
			'tab' => 'content',
			'group' => 'filter',
			'label' => esc_html__( 'Typography', 'bricks' ),
			'type' => 'typography',
			'css' => [
				[
					'property' => 'typography',
					'selector' => '.wbe-radio-toolbar label',
				],
			],
			'inline' => true,
		];

		$this->controls['filterBackgroundColor'] = [
			'tab' => 'content',
			'group' => 'filter',
			'label' => esc_html__( 'Background Color', 'bricks' ),
			'type' => 'color',
			'inline' => true,
			'css' => [
				[
				  'property' => 'background-color',
				  'selector' => '.wbe-radio-toolbar label',
				]
			],
		];

		$this->controls['filterBorder'] = [
		  'tab' => 'content',
		  'group' => 'filter',
		  'label' => esc_html__( 'Border', 'bricks' ),
		  'type' => 'border',
		  'css' => [
			[
			  'property' => 'border',
			  'selector' => '.wbe-radio-toolbar label',
			],
		  ],
		  'inline' => true,
		  'small' => true,
		];

		$terms = get_terms( array( 'taxonomy' => 'category', 'orderby' => 'name' ) );
		foreach ( $terms as $term ) {
			$this->controls[$term->slug . 'titleSeparator'] = [
				'tab'   => 'content',
				'group' => 'categories',
				'label' => esc_html__( $term->name, 'bricks' ),
				'type'  => 'separator',
			];

			$this->controls[$term->slug . 'BackgroundColor'] = [
				'tab' => 'content',
				'group' => 'categories',
				'label' => esc_html__( 'Background Color', 'bricks' ),
				'type' => 'color',
				'inline' => true,
				'css' => [
					[
					  'property' => 'background-color',
					  'selector' => '.' . $term->slug,
					]
				],
			];

			$this->controls[$term->slug . 'Typography'] = [
			  'tab' => 'content',
			  'group' => 'categories',
			  'label' => esc_html__( 'Typography', 'bricks' ),
			  'type' => 'typography',
			  'css' => [
				[
				  'property' => 'typography',
				  'selector' => '.' . $term->slug,
				],
			  ],
			  'inline' => true,
			];
		}
	}

	public function enqueue_scripts() {
		wp_enqueue_script( 'wbe-posts-filter', plugin_dir_url( __FILE__ ) . 'posts-filter.js', null, '1.0', true );
		wp_enqueue_style( 'wbe-posts-filter', plugin_dir_url( __FILE__ ) . 'posts-filter.css' );
	}

	public function render() {	
		$this->set_attribute( '_root', 'class', 'wbe-element-container' );
		echo "<{$this->tag} {$this->render_attributes( '_root' )}>";
		echo '<form action="' . esc_url( site_url() ) . '/wp-admin/admin-ajax.php" method="POST" id="filterForm">';
		
			if( $terms = get_terms( array( 'taxonomy' => 'category', 'orderby' => 'name' ) ) ) {
				$output = "";

				echo '<div class="wbe-radio-toolbar">';

				foreach ( $terms as $term ) {
					$input = '<input id="' . esc_html( $term->name ) . '" type="radio" class="wbe-posts-filter" name="posts_category" value="' . esc_html( $term->term_id ) . '" />';
					$label =  '<label for="' . esc_html( $term->name ) . '" class="wbe-radio-filter">' . esc_html( $term->name ) . " (" . esc_html( $term->count ) . ') </label>';

					$output .= $input . $label;
				}

				echo '<input id="all" type="radio" class="wbe-posts-filter" name="posts_category" value="-1" checked />';
				echo '<label for="all">All (' . wp_count_posts( $post_type = 'post' )->publish . ')</label>';
				echo $output;
				echo '</select>';
				echo '</div>';
			}

			if ( ! empty( $this->settings['infiniteScroll'] ) ) {
				echo "<input type='hidden' name='infinite_scroll' value='" . $this->settings['infiniteScroll'] . "'>";
			} else {
				echo "<input type='hidden' name='infinite_scroll' value='0'>";
			}

			echo "<input type='hidden' name='post_type' value='" . $this->settings['post_type']. "'>";
			echo "<input type='hidden' name='posts_per_page' value='" . $this->settings['postsPerPage']. "'>";
			echo '<input type="hidden" name="action" value="filterposts">';
			echo '</form>';
			echo '<div id="wbe-response"/>';

			echo "</{$this->tag}>";
	}

	public function filter_posts(){
		$paged = 1;
		$posts_per_page = 9;
		$post_type = 'post';

		if( isset($_POST['paged'])) {
			$paged = sanitize_text_field( $_POST['paged'] );
		}

		if( isset($_POST['posts_per_page'])) {
			$posts_per_page = sanitize_text_field( $_POST['posts_per_page'] );
		}

		if( isset($_POST['post_type'])) {
			$post_type = sanitize_text_field( $_POST['post_type'] );
		}

		$args = array(
			'post_type' => $post_type,
			'post_status' => 'publish',
			'paged' => $paged,
			'posts_per_page' => $posts_per_page,
			'orderby' => 'rand(1234)'
		);

		if( isset( $_POST['posts_category'] ) &&  $_POST['posts_category'] != -1 )
			$args['tax_query'] = array(
			array(
				'taxonomy' => 'category',
				'field' => 'id',
				'terms' => sanitize_text_field( $_POST['posts_category'] )
			)
		);

		$query = new WP_Query( $args );

		if( $query->have_posts() ) {

			if ( $paged == 1 ) {
				echo '<div class="wbe-posts">';
			}

			while ( $query->have_posts() ) {
				$query->the_post(); ?>

				<div class="wbe-post wbe-loading wbe-lazy">

					<div class="wbe-post__body-container">
						<div class="wbe-post__category-container">
							<?php 
								foreach ( ( get_the_category() ) as $category ) {
									echo '<div class="wbe-post__category ' . esc_html( $category->slug ) . '">' . esc_html( $category->cat_name ) . '</div>';
								} 
							?>
						</div>

						<h4 class="wbe-post__heading">
							<a href="<?php esc_url( the_permalink() ); ?>"><?php esc_html( the_title() ); ?></a>
						</h4>

						<p class="wbe-post__text">
							<?php echo  esc_html( get_the_excerpt() ); ?>
						</p> 
					</div>

					<div class="wbe-post__image-wrapper">
						<?php
							$id = get_post_thumbnail_id();
							$attachment = wp_get_attachment_image_src( $id, 'full');
							$src 	= wp_get_attachment_image_url( $id, 'full' );
							$srcset = wp_get_attachment_image_srcset( $id, 'full' );
							$sizes 	= wp_get_attachment_image_sizes( $id, 'full' );

							echo '<img class="wbe-post__image" data-src="'. $src .'" data-srcset="' . $srcset . '" sizes="' . $sizes . '" />';
						?>
					</div>

					<div class="wbe-post__footer">
						<p class="wbe-post__link">Read More</p>
					</div>
				</div>
				<?php
			}

			if ( $paged == 1 ) {
				echo '</div>';

				if ($query->max_num_pages > 1 && $paged < $query->max_num_pages) {
					echo '<button 
							class="wbe-posts__load-more" 
							data-current-page="'. esc_html( $paged ) .'" 
							data-next-page="'. esc_html( ($paged + 1) ) .'" 
							data-max-page="'. esc_html( $query->max_num_pages ) .'" 
							onClick="loadMore()">Load more</button>';
				}
			}

			wp_reset_postdata();
		}
		else {
			echo 'No posts found';
		}

		die();
	}

	static function get_registered_post_types() {
		$registered_post_types = get_post_types(
			[ 'public' => true ],
			'objects'
		);

		// Remove post types
		unset( $registered_post_types['attachment'] );
		unset( $registered_post_types['product'] );
		unset( $registered_post_types[ BRICKS_DB_TEMPLATE_SLUG ] ); // Bricks templates always have builder support

		$post_types = [];

		foreach ( $registered_post_types as $key => $object ) {
			$post_types[ $key ] = $object->label;
		}

		return $post_types;
	}

}
