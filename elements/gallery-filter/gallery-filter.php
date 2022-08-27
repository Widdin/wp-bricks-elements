<?php 

if ( ! defined( 'ABSPATH' ) ) exit;

class Wbe_Gallery_Filter extends \Bricks\Element {

	public $category     = 'widdin-wbe';
	public $name         = 'wbe-gallery-filter';
	public $icon         = 'ti-layout-list-thumb';
	public $css_selector = '.wbe-gallery-filter-wrapper';
	public $scripts      = ['wbeGallerySendAJAX'];


	public function get_label() {
		return esc_html__( 'Gallery Filter', 'bricks' );
	}
	
	public function __construct( $element = null ) {
		parent::__construct( $element );

		add_action('wp_ajax_filtergallery', [$this, 'filter_gallery']); 
		add_action('wp_ajax_nopriv_filtergallery', [$this, 'filter_gallery']);
	}
	
	public function set_control_groups() {
		$this->control_groups['filterActive'] = [
			'title' => esc_html__( 'Filter Active', 'bricks' ),
			'tab' => 'content',
		];

		$this->control_groups['filter'] = [
			'title' => esc_html__( 'Filter', 'bricks' ),
			'tab' => 'content',
		];
	}

	public function set_controls() {
		$this->controls['settingsInfo'] = [
			'type'    => 'info',
			'content' => esc_html__( 'Select image size and the categories you want to show, then click "SAVE SETTINGS".', 'bricks' ),
		];

		$this->controls['categoryWhitelist'] = [
			'tab' => 'content',
			'label' => esc_html__( 'Categories', 'bricks' ),
			'type' => 'select',
			'options' => $this->get_happyfiles_terms_options(),
			'inline' => true,
			'placeholder' => esc_html__( 'Select', 'bricks' ),
			'multiple' => true,
			'searchable' => true,
			'clearable' => true,
		];

		$this->controls['imageSize'] = [
			'tab'      => 'content',
			'label'    => esc_html__( 'Image size', 'bricks' ),
			'type'     => 'select',
			'options'  => $this->control_options['imageSizes'],
			'default' => 'full',
		];

		$this->controls['filterActiveTypography'] = [
			'tab' => 'content',
			'group' => 'filterActive',
			'label' => esc_html__( 'Typography', 'bricks' ),
			'type' => 'typography',
			'css' => [
				[
					'property' => 'typography',
					'selector' => '.wbe-gallery__radio-toolbar input[type="radio"]:checked+label',
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
				  'selector' => '.wbe-gallery__radio-toolbar input[type="radio"]:checked+label',
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
			  'selector' => '.wbe-gallery__radio-toolbar input[type="radio"]:checked+label',
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
					'selector' => '.wbe-gallery__radio-toolbar label',
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
				  'selector' => '.wbe-gallery__radio-toolbar label',
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
			  'selector' => '.wbe-gallery__radio-toolbar label',
			],
		  ],
		  'inline' => true,
		  'small' => true,
		];

		$this->controls['apply'] = [
			'type'   => 'apply',
			'reload' => true,
			'label'  => esc_html__( 'Save settings', 'bricks' ),
		];
	}

	public function enqueue_scripts() {
		wp_enqueue_script( 'wbe-gallery-filter', plugin_dir_url( __FILE__ ) . 'gallery-filter.js', null, '1.0', true );
		wp_enqueue_style( 'wbe-gallery-filter', plugin_dir_url( __FILE__ ) . 'gallery-filter.css' );
		wp_enqueue_script('masonry');
	}

	public function render() {	
		if ( !$this->isHappyFilesActive() ) {
			echo "HappyFiles is not installed/activated."; 
			return;
		}

		$this->set_attribute( '_root', 'class', 'wbe-gallery-filter-container' );
		echo "<{$this->tag} {$this->render_attributes( '_root' )}>";
		?>

		<form action="<?php echo esc_url( site_url() ) ?>/wp-admin/admin-ajax.php" method="POST" id="filter">
		<?php		
			$include = isset($this->settings['categoryWhitelist']) ? $this->settings['categoryWhitelist'] : -1;
			$terms_args = array( 'taxonomy' => 'happyfiles_category', 'include' => $include );
			$terms = get_terms( $terms_args );

			if ( $terms ) {
				echo '<div class="wbe-gallery__radio-toolbar">';

				$output = '';
				$count_total = array_sum ( array_column( $terms , 'count' ) );

				foreach( $terms as $term ) {
					$input = '<input id="' . esc_html( $term->slug ) . '" type="radio" class="wbe-gallery__gallery-filter" name="gallery_category" value="' . esc_html( $term->term_id ) . '" />';
					$label =  '<label for="' . esc_html( $term->slug ) . '" class="wbe-gallery__radio-filter">' . esc_html( $term->name ) . " (" . esc_html( $term->count ). ') </label>';
					$output .= $input . $label;
				}

				echo '<input id="all" type="radio" class="wbe-gallery__gallery-filter" name="gallery_category" value="-1" checked />';
				echo '<label for="all">All (' . esc_html( $count_total ) . ')</label>';
				echo $output;
				echo '</div>';
			} else {
				echo 'No categories found.';
			}
		?>
		<input type="hidden" name="postId" value="<?= $this->post_id ?>">
		<input type="hidden" name="formId" value="<?= $this->id ?>">
		<input type="hidden" name="action" value="filtergallery">
		</form>

		<div id="response" class="wbe-gallery" />
		<?php

		echo "</{$this->tag}>";
	}

	public function filter_gallery(){
		$settings = \Bricks\Helpers::get_element_settings( sanitize_text_field($_POST['postId']), sanitize_text_field($_POST['formId']) );

		$include = isset($settings['categoryWhitelist']) ? $settings['categoryWhitelist'] : null;

		if ( is_null ( $include ) ) {
			echo "No categories selected.";
			die();
		}

		$terms_args = array( 'taxonomy' => 'happyfiles_category', 'fields' => 'ids' , 'include' => $include );

		$terms = get_terms( $terms_args );

		if ( isset( $_POST['gallery_category'] ) && $_POST['gallery_category'] != '-1' && in_array( $_POST['gallery_category'], $include )) {
			$term_id = sanitize_text_field( $_POST['gallery_category'] );  
			$terms = get_term_by( 'id', $term_id, 'happyfiles_category' )->term_id;
		}

		$image_size = isset( $settings['imageSize'] ) ? $settings['imageSize'] : 'large';

		$args = array(
			'post_type' => 'attachment',
			'post_status' => 'inherit',
			'posts_per_page' => -1,
			'post_mime_type' => 'image/jpeg,image/gif,image/jpg,image/png',
			'tax_query' => array(
				array(
					'taxonomy' => 'happyfiles_category',
					'field' => 'id',
					'terms' => $terms,
				)
			)
		);

		$query = new WP_Query( $args );

		if ( $query->have_posts() ) {
			$images = array_column( $query->posts, 'ID' );

			shuffle( $images );

			foreach($images as $index => $id) {
				$atts = wp_get_attachment_image_src( $id, $image_size);
				$src 	= wp_get_attachment_image_url( $id , $image_size);
				$srcset = wp_get_attachment_image_srcset( $id , $image_size);
				$sizes 	= wp_get_attachment_image_sizes( $id , $image_size);

				echo '<img style="aspect-ratio: '.esc_html( $atts[1].'/'.$atts[2] ) .';" class="lazy wbe-gallery__loading wbe-gallery__image" data-src="'. $src .'" data-srcset="' . $srcset . '" sizes="' . esc_attr( $sizes ) . '" />';
			}

			wp_reset_postdata();
		} else {
			echo 'No images found';
		}

		die();
	}

	public static function get_happyfiles_terms_options() {
		$args = array( 'taxonomy' => 'happyfiles_category'  );

		$terms = get_terms( $args );

		$term_options = [];

		foreach ( $terms as $term ) {
			$term_options[ $term->term_id ] = $term->name;
		}

		return $term_options;
	}

	public function isHappyFilesActive() {
		return defined('HAPPYFILES_VERSION') ? true : false;
	}
}
