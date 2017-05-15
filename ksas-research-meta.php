<?php
/*
Plugin Name: KSAS Research Metabox for Profiles
Plugin URI: http://krieger.jhu.edu/communications/web/plugins/profiles
Description: Creates the metabox for research profile details.  This is for use on Woodrow Wilson and DURA.
Version: 1.0
Author: Cara Peckens
Author URI: mailto:cpeckens@jhu.edu
License: GPL2
*/
$research_meta_metabox = array( 
	'id' => 'research_meta',
	'title' => 'Research Profile Details',
	'page' => array('profile'),
	'context' => 'normal',
	'priority' => 'high',
	'fields' => array(
				array(
					'name' 			=> 'Last Name',
					'desc' 			=> 'For indexing',
					'id' 			=> 'ecpt_award_alpha',
					'class' 		=> 'ecpt_award_alpha',
					'type' 			=> 'text',
					'rich_editor' 	=> 0,			
					'max' 			=> 0,
					'std'			=> ''													
				),
				array(
					'name' 			=> 'Award Type/Name',
					'desc' 			=> '',
					'id' 			=> 'ecpt_award_name',
					'class' 		=> 'ecpt_award_name',
					'type' 			=> 'text',
					'rich_editor' 	=> 0,			
					'max' 			=> 0,
					'std'			=> ''													
				),
				array(
					'name' 			=> 'Year',
					'desc' 			=> '',
					'id' 			=> 'ecpt_class_year',
					'class' 		=> 'ecpt_class_year',
					'type' 			=> 'text',
					'rich_editor' 	=> 0,			
					'max' 			=> 0,
					'std'			=> ''													
				),
				
				array(
					'name' 			=> 'Upload Research PDF',
					'desc' 			=> '',
					'id' 			=> 'ecpt_research_pdf',
					'class' 		=> 'ecpt_research_pdf',
					'type' 			=> 'upload',
					'rich_editor' 	=> 0,			
					'max' 			=> 0,
					'std'			=> ''													
				),
				array(
					'name' 			=> 'Video Link',
					'desc' 			=> 'Enter the URL for the video ie. http://youtu.be/57EDxvldLD4',
					'id' 			=> 'ecpt_video',
					'class' 		=> 'ecpt_video',
					'type' 			=> 'text',
					'rich_editor' 	=> 0,			
					'max' 			=> 0,
					'std'			=> ''													
				),				array(
					'name' 			=> 'Articles',
					'desc' 			=> 'Create an unordered list of news articles and links',
					'id' 			=> 'ecpt_article_list',
					'class' 		=> 'ecpt_article_list',
					'type' 			=> 'textarea',
					'rich_editor' 	=> 1,			
					'max' 			=> 0,
					'std'			=> ''													
				),
				

				
));			
			
add_action('admin_menu', 'ecpt_add_research_meta_meta_box');
function ecpt_add_research_meta_meta_box() {

	global $research_meta_metabox;		

	foreach($research_meta_metabox['page'] as $page) {
		add_meta_box($research_meta_metabox['id'], $research_meta_metabox['title'], 'ecpt_show_research_meta_box', $page, 'normal', 'default', $research_meta_metabox);
	}
}

// function to show meta boxes
function ecpt_show_research_meta_box()	{
	global $post;
	global $research_meta_metabox;
	global $ecpt_prefix;
	global $wp_version;
	
	// Use nonce for verification
	echo '<input type="hidden" name="ecpt_research_meta_meta_box_nonce" value="', wp_create_nonce(basename(__FILE__)), '" />';
	
	echo '<table class="form-table">';

	foreach ($research_meta_metabox['fields'] as $field) {
		// get current post meta data

		$meta = get_post_meta($post->ID, $field['id'], true);
		
		echo '<tr>',
				'<th style="width:20%"><label for="', $field['id'], '">', $field['name'], '</label></th>',
				'<td class="ecpt_field_type_' . str_replace(' ', '_', $field['type']) . '">';
		switch ($field['type']) {
			case 'text':
				echo '<input type="text" name="', $field['id'], '" id="', $field['id'], '" value="', $meta ? $meta : $field['std'], '" size="30" style="width:97%" /><br/>', '', $field['desc'];
				break;
			case 'textarea':
			
				if($field['rich_editor'] == 1) {
					if($wp_version >= 3.3) {
						echo wp_editor($meta, $field['id'], array('textarea_name' => $field['id'], 'wpautop' => false));
					} else {
						// older versions of WP
						$editor = '';
						if(!post_type_supports($post->post_type, 'editor')) {
							$editor = wp_tiny_mce(true, array('editor_selector' => $field['class'], 'remove_linebreaks' => false) );
						}
						$field_html = '<div style="width: 97%; border: 1px solid #DFDFDF;"><textarea name="' . $field['id'] . '" class="' . $field['class'] . '" id="' . $field['id'] . '" cols="60" rows="8" style="width:100%">'. $meta . '</textarea></div><br/>' . __($field['desc']);
						echo $editor . $field_html;
					}
				} else {
					echo '<div style="width: 100%;"><textarea name="', $field['id'], '" class="', $field['class'], '" id="', $field['id'], '" cols="60" rows="8" style="width:97%">', $meta ? $meta : $field['std'], '</textarea></div>', '', $field['desc'];				
				}
				
				break;
			case 'upload':
				echo '<input type="text" class="ecpt_upload_field" name="', $field['id'], '" id="', $field['id'], '" value="', $meta ? $meta : $field['std'], '" size="30" style="width:80%" /><input class="ecpt_upload_image_button" type="button" value="Upload" /><br/>', '', $field['desc'];
				break;
		}
		echo     '<td>',
			'</tr>';
	}
	
	echo '</table>';
}	

add_action('save_post', 'ecpt_research_meta_save');

// Save data from meta box
function ecpt_research_meta_save($post_id) {
	global $post;
	global $research_meta_metabox;
	
	// verify nonce
	if (!isset($_POST['ecpt_research_meta_meta_box_nonce']) || !wp_verify_nonce($_POST['ecpt_research_meta_meta_box_nonce'], basename(__FILE__))) {
		return $post_id;
	}

	// check autosave
	if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
		return $post_id;
	}

	// check permissions
	if ('page' == $_POST['post_type']) {
		if (!current_user_can('edit_page', $post_id)) {
			return $post_id;
		}
	} elseif (!current_user_can('edit_post', $post_id)) {
		return $post_id;
	}
	
	foreach ($research_meta_metabox['fields'] as $field) {
	
		$old = get_post_meta($post_id, $field['id'], true);
		$new = $_POST[$field['id']];
		
		if ($new && $new != $old) {
			if($field['type'] == 'date') {
				$new = ecpt_format_date($new);
				update_post_meta($post_id, $field['id'], $new);
			} else {
				update_post_meta($post_id, $field['id'], $new);
				
				
			}
		} elseif ('' == $new && $old) {
			delete_post_meta($post_id, $field['id'], $old);
		}
	}
}


add_filter( 'manage_edit-profile_columns', 'my_profile_columns' ) ;

function my_profile_columns( $columns ) {

	$columns = array(
		'cb' => '<input type="checkbox" />',
		'title' => __( 'Name' ),
		'affiliations' => __( 'Affiliations' ),
		'year' => __('Year' ),
		'indexing' => __('Index Name' ),
		'category' => __('Category' ),
		'date' => __( 'Date' )
	);

	return $columns;
}

add_action( 'manage_profile_posts_custom_column', 'my_manage_profile_columns', 10, 2 );

function my_manage_profile_columns( $column, $post_id ) {
	global $post;

	switch( $column ) {

		/* If displaying the 'role' column. */
		case 'affiliations' :

			/* Get the roles for the post. */
			$terms = get_the_terms( $post_id, 'affiliation' );
			$terms2 = get_the_terms( $post_id, 'academicdepartment' );
			/* If terms were found. */
			if ( !empty( $terms ) || !empty( $terms2 ) ) {
				
				$out = array();
				$out2 = array();

				if ( !empty( $terms ) ) {
					/* Loop through each term, linking to the 'edit posts' page for the specific term. */
					foreach ( $terms as $term ) {
						$out[] = sprintf( '<a href="%s">%s</a>',
							esc_url( add_query_arg( array( 'post_type' => $post->post_type, 'affiliation' => $term->slug ), 'edit.php' ) ),
							esc_html( sanitize_term_field( 'name', $term->name, $term->term_id, 'affiliation', 'display' ) )
						);
					}
				}
				
				if ( !empty( $terms2) ) {
					foreach ( $terms2 as $term2 ) {
						$out2[] = sprintf( '<a href="%s">%s</a>',
							esc_url( add_query_arg( array( 'post_type' => $post->post_type, 'academicdepartment' => $term2->slug ), 'edit.php' ) ),
							esc_html( sanitize_term_field( 'name', $term2->name, $term2->term_id, 'academicdepartment', 'display' ) )
						);
					}
				}
			
				$all_terms = array_merge($out, $out2);
				/* Join the terms, separating them with a comma. */
				echo join( ', ', $all_terms );
			}

			/* If no terms were found, output a default message. */
			else {
				_e( 'No Affiliations' );
			}

			break;

		case 'category' :

		$terms3 = get_the_terms( $post_id, 'category' );

					/* If terms were found. */
			if ( !empty( $terms3 ) ) {

				$out3 = array();

				/* Loop through each term, linking to the 'edit posts' page for the specific term. */
				foreach ( $terms3 as $term3 ) {
					$out3[] = sprintf( '<a href="%s">%s</a>',
						esc_url( add_query_arg( array( 'post_type' => $post->post_type, 'category' => $term3->slug ), 'edit.php' ) ),
						esc_html( sanitize_term_field( 'name', $term3->name, $term3->term_id, 'category', 'display' ) )
					);
				}

				/* Join the terms, separating them with a comma. */
				echo join( ', ', $out3 );
			}

			/* If no terms were found, output a default message. */
			else {
				_e( 'No Category Assigned' );
			}

		break;	

		case 'year' :
			
			/* Get the thumbnail */
			$award_year = get_post_meta($post->ID, 'ecpt_class_year', true);

			if ( empty( $award_year ) )
				echo __( 'No Year Assigned' );

			/* If there is a duration, append 'minutes' to the text string. */
			else
				
				echo $award_year;

			break;
		case 'indexing' :
			
			/* Get the thumbnail */
			$index_name = get_post_meta($post->ID, 'ecpt_award_alpha', true);

			if ( empty( $index_name ) )
				echo __( 'No Name Given' );

			/* If there is a duration, append 'minutes' to the text string. */
			else
				
				echo $index_name;

			break;

		/* Just break out of the switch statement for everything else. */
		default :
			break;
	}
}