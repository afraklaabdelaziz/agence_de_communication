<?php 
/*!
 * System Assistant: Placeholder map page
 */

global $zbs;

$placeholders_class = $zbs->get_templating();
$placeholders = $placeholders_class->get_placeholders();

jpcrm_render_system_title( __( 'Placeholders', 'zero-bs-crm' ) );


/**
 * Render a single placeholder
 */
function jpcrm_render_placeholder_line( $placeholder_key = '', $placeholder_info = array() ){

	?><div class="ui segment">

		<h4 class="ui dividing header"><code><?php echo $placeholder_key; ?></code> - <?php if ( isset( $placeholder_info['description'] ) ) echo $placeholder_info['description']; ?></h4>

		<?php

			// got expected format?
			if ( isset( $placeholder_info['expected_format'] ) ){

				$format_prefix = '<p>' . __( 'Format', 'zero-bs-crm' ) . ': <span class="ui label">';
				$format_suffix = '</span></p>';

				switch ($placeholder_info['expected_format']){

					case 'str':
					case 'text':
					case 'textarea':
					case 'select':
					case 'radio':
						echo $format_prefix . __( 'Text String', 'zero-bs-crm' ) . $format_suffix;
						break;
					case 'tel':
						echo $format_prefix . __( 'Telephone', 'zero-bs-crm' ) . $format_suffix;
						break;
					case 'email':
						echo $format_prefix . __( 'Email', 'zero-bs-crm' ) . $format_suffix;
						break;
					case 'uts':
						echo $format_prefix . __( 'Unix Timestamp / Date', 'zero-bs-crm' ) . $format_suffix;
						break;
					case 'int':
					case 'float':
						echo $format_prefix . __( 'Number', 'zero-bs-crm' ) . $format_suffix;
						break;
					case 'bool':
						echo $format_prefix . __( 'Boolean', 'zero-bs-crm' ) . $format_suffix;
						break;
					case 'html':
						echo $format_prefix . __( 'HTML', 'zero-bs-crm' ) . $format_suffix;
						break;
					case 'curr':
						echo $format_prefix . __( 'Currency String (e.g. USD)', 'zero-bs-crm' ) . $format_suffix;
						break;

					default:
						// ?
						break;

				}

			}

			// got available in?
			if ( isset( $placeholder_info['available_in'] ) && count( $placeholder_info['available_in'] ) > 0 ){

				echo '<p>' . __( 'Available in areas', 'zero-bs-crm' ) . ': ';

				$available_in_count = 0;

				foreach ( $placeholder_info['available_in'] as $available_in ){

					if ( $available_in_count > 0 ) echo ', ';

					echo '<span class="ui label blue">' . __( ucwords( $available_in ), 'zero-bs-crm' ) . '</span>';

					$available_in_count++;

				}

				echo '</p>';
			}

			// got aliases?
			if ( isset( $placeholder_info['aliases'] ) && count ( $placeholder_info['aliases'] ) > 0 ){

				echo '<p>' . __( 'Aliases', 'zero-bs-crm' ) . ': ';

				$alias_count = 0;

				foreach ( $placeholder_info['aliases'] as $alias ){

					if ( $alias_count > 0 ) echo ', ';

					echo '<span class="ui label yellow">' . $alias . '</span>';

					$alias_count++;

				}

				echo '</p>';
			}

		?>

	</div><?php

}

?>
<p><?php _e( 'Throughout the CRM you can use various placeholders to represent fields (e.g. in quote templates or when sending out emails). This page lists all placeholders which are available to you with your current setup.', 'zero-bs-crm' ); ?></p>

<div class="ui styled fluid accordion">
  
	<?php 

		$active_group = '';

		if ( is_array( $placeholders ) ){

			foreach ( $placeholders as $placeholder_group_key => $placeholder_group ){

				// heading, e.g. contacts
				?><div class="title"><i class="dropdown icon"></i> <?php echo ucwords( __( $placeholder_group_key, 'zero-bs-crm' ) ); ?></div><?php
				?><div class="<?php if ( $active_group == $placeholder_group_key ) { echo 'active'; } ?> content"><?php

				$placeholder_group_prefix = '';

				// all objtypes basically
				if ( $zbs->DAL->isValidObjTypeID( $zbs->DAL->objTypeID( $placeholder_group_key ) ) ) {

					//$placeholder_group_prefix = $placeholder_group_key . '-';

				}

				foreach ( $placeholder_group as $placeholder_key => $placeholder ){

					$key = '##' . strtoupper( $placeholder_group_prefix . $placeholder_key ) . '##';

					// render
					jpcrm_render_placeholder_line( $key, $placeholder );

				
				}

				?></div><?php

			}


		}

	?>

</div>
