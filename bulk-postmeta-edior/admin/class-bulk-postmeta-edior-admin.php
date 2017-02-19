<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://profiles.wordpress.org/psdtofinal
 * @since      1.0.0
 * @package    Bulk_Postmeta_Edior
 * @subpackage Bulk_Postmeta_Edior/admin
 * @author     PSD to Final <info@psdtofinal.com>
 */
class Bulk_Postmeta_Edior_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since      1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version = $version;
		add_action('admin_menu', array( $this , 'add_admin_pages'));
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/bulk-postmeta-edior-admin.css', array(), $this->version, 'all' );
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		// Nothing to see here, yet :)
	}
	
	/**
	 * Adds the editor menu
	 * 
	 * @since 1.0.0
	 */
	public function add_admin_pages() {
		
		// Add the top level "List editables" page
		add_menu_page(
			__('Bulk Edit'), 
			__('Bulk Edit'), 
			'delete_others_posts', 
			'bulk_edit_list_editables',
			array(&$this,'list_editables'),
			'dashicons-category',
			21
		);
		
		// Grab existing bulk edit's
		global $wpdb;
		$sql = "SELECT bulk.*
				FROM {$wpdb->prefix}bulk_edit AS bulk
				ORDER BY meta_key ASC";
		$bulk_edits = $wpdb->get_results($sql);
		
		if (!empty($bulk_edits)) {
			foreach ($bulk_edits AS $bulk_edit) {
				add_submenu_page(
					'bulk_edit_list_editables', 
					'Edit <i>'.$bulk_edit->meta_label.'</i>', 
					'Edit <i>'.$bulk_edit->meta_label.'</i>', 
					'delete_others_posts',
					'bulk_edit_meta_'.$bulk_edit->meta_key,
					array(&$this,'bulk_edit_meta')
				);
			}
		}
		
		// Add the settings page
		add_submenu_page(
			'bulk_edit_list_editables', 
			'Bulk Editor Settings', 
			'Settings', 
			'activate_plugins',
			'bulk_edit_admin_settings',
			array(&$this,'admin_settings')
		);
	}
	
	/**
	 * Creates a list of the post meta types that have been set up for editing
	 * 
	 * @since 1.0.0
	 */
	public function list_editables() {
		?>
		<div class="wrap">
			<h2>Bulk Editor</h2>
			
			<?php
			
			// Grab existing bulk edit's
			global $wpdb;
			$sql = "SELECT bulk.*
					FROM {$wpdb->prefix}bulk_edit AS bulk
					ORDER BY meta_key ASC";
			$bulk_edits = $wpdb->get_results($sql);
			
			if (!empty($bulk_edits)) : ?>
				<p>Set the fields you'd like to bulk edit</p>
				<ul class="bulk-postmeta-list">			
				<?php foreach ($bulk_edits AS $bulk_edit) : ?>
					<li>
						<a href="admin.php?page=bulk_edit_meta_<?php echo $bulk_edit->meta_key ?>"><?php echo $bulk_edit->meta_label ?></a>
					</li>
				<?php endforeach; ?>
				</ul>
			<?php else : ?>
				<?php $this->no_fields() ?>
			<?php endif; ?>
		</div>
		<?php
	}
	
	/**
	 * Creates the settings page
	 * 
	 * @since 1.0.0
	 */
	public function admin_settings() {
		// Grab the DB object and any helpers
		global $wpdb;
		$base_uri = 'admin.php?page=bulk_edit_admin_settings';
			
		// Check for actions				
		if (!empty($_REQUEST['new_meta_key']) || !empty($_REQUEST['new_meta_label']) || !empty($_REQUEST['new_field_type']) || !empty($_REQUEST['post_types'])) {
			$this->save_new_option();
		}
		if (!empty($_REQUEST['delete'])) {
			$this->delete_option();
		}
		?>
		<div class="wrap">		
			<h2>Bulk Postmeta Settings</h2>
			<form method="post" action="<?php echo $base_uri ?>">
				<fieldset>
					<?php
					// Grab existing bulk edit's
					$sql = "SELECT bulk.*
							FROM {$wpdb->prefix}bulk_edit AS bulk
							ORDER BY meta_key ASC";
					$bulk_edits = $wpdb->get_results($sql);
					
					$done = array();
					if (!empty($bulk_edits)) : ?>
						<table class="bulk-postmeta-admin wp-list-table widefat fixed striped">
							<thead>
								<tr>
									<th>Meta Key</th>
									<th>Field Label</th>
									<th>Field Type</th>
									<th>Post Type(s)</th>
									<th>&nbsp;</th>
								</tr>
							</thead>
							<tbody>
								<?php
								foreach ($bulk_edits AS $bulk_edit) {
									?>
									<tr>
										<td><?php echo $bulk_edit->meta_key ?></td>
										<td><?php echo $bulk_edit->meta_label ?></td>
										<td><?php echo $bulk_edit->field_type ?></td>
										<td><?php echo ucwords(str_replace(array('-','_'),' ',$bulk_edit->post_types)) ?></td>
										<td>
											<a class="bulk-edit" href="admin.php?page=bulk_edit_meta_<?php echo $bulk_edit->meta_key ?>" title="Edit Fields"><span class="dashicons dashicons-edit"></span></a>
											<a class="bulk-delete" href="<?php echo "{$base_uri}&amp;delete={$bulk_edit->meta_key}"?>" title="Delete <?php echo $bulk_edit->meta_label ?>"><span class="dashicons dashicons-dismiss"></span></a>
										</td>
									</tr>
									<?php
									$done[] = $bulk_edit->meta_key;
								}
								
								?>
							</tbody>
						</table>
						
					<?php else : ?>
						<h3>Getting Started</h3>
						<p>The first thing you need to do is create a bulk edit set, below:</p>
						<ol type="1">
							<li>From the drop-down box below, select the <code>meta_key</code> you'd like to bulk edit</li>
							<li>Enter a friendly name or label in the &quot;Field Label&quot; box</li>
							<li>Select the editor type (plain text input, paragraph text, or a &quot;Content Editor&quot; style of input)</li>
							<li>Check / select the type of posts you'd like this to apply to (eg, Posts, Pages, or custom post types)</li>
							<li>Click <i>Add New</i></li>
						</ol>
						<p>Once you've added your first bulk editor set, you will be able to access the editors by clicking the edit icon <span class="dashicons dashicons-edit"></span> from a list that will appear in place of this message; or directly from the <i>Bulk Edit</i> menu, on the left.</p>
					<?php endif;
					
					// Next, set up potential new edits	
					if (!empty($done)) {
						$skip = ltrim(str_repeat(',%s', count($done)),',');						
						$sql = "SELECT DISTINCT meta_key
								FROM {$wpdb->prefix}postmeta
								WHERE meta_key <> ''
								AND meta_key NOT IN ({$skip})
								ORDER BY meta_key ASC";
						$meta_keys = $wpdb->get_results($wpdb->prepare($sql,$done));
					} else {
						$sql = "SELECT DISTINCT meta_key
								FROM {$wpdb->prefix}postmeta
								WHERE meta_key <> ''
								ORDER BY meta_key ASC";
						$meta_keys = $wpdb->get_results($sql);
					}
					
					if (!empty($meta_keys)) : ?>
						<p>&nbsp;</p>
						<hr />
						<h3>Add New Key</h3>
						<p>
							<select id="new_meta_key" name="new_meta_key">
								<option value="">Select meta key...</option>
								<?php foreach ($meta_keys AS $meta_key) : ?>
									<option value="<?php echo $meta_key->meta_key ?>"><?php echo $meta_key->meta_key ?></option>
								<?php endforeach; ?>
							</select>
						</p>
						<p>
							<input type="text" id="new_meta_label" name="new_meta_label" value="" placeholder="Field Label (Used on Menu)" />
						</p>
						<p>
							<select id="new_field_type" name="new_field_type">
								<option value="">Select editor type...</option>
								<option value="Single of Text">Single Line of Text</option>
								<option value="Block of Text">Block of Text</option>
								<option value="Basic HTML">Basic HTML</option>
								<option value="Full HTML with Media">Full HTML with Media</option>
							</select>
						</p>
						<p><strong>Select the Post Types you'd like to edit:</strong></p>
						<?php
						$sql = "SELECT DISTINCT post_type 
								FROM {$wpdb->prefix}posts 
								WHERE post_type <> '' 
								AND post_type <> 'revision'
								AND post_type <> 'attachment'
								AND post_type <> 'wpcf7_contact_form'
								AND post_type <> 'nav_menu_item'
								AND post_type <> 'optionsframework'
								ORDER BY post_type ASC";
						$post_types = $wpdb->get_results($sql);
					
						foreach ($post_types AS $post_type) : ?>
							<p>
								<input type="checkbox" 
									name="post_types[<?php echo $post_type->post_type ?>]" 
									id="post_types[<?php echo $post_type->post_type ?>]" 
									value="<?php echo $post_type->post_type ?>" />
								<label for="post_types[<?php echo $post_type->post_type ?>]">
									<?php echo ucwords(str_replace(array('-','_'),' ',$post_type->post_type)) ?>
								</label>
							</p>
						<?php endforeach; ?>
						<?php 
						submit_button('Add New');
					endif;
					?>
				</fieldset>
			</form>
		</div>
		<?php
	}

	/**
	 * Saves a new editor set
	 * 
	 * @since 1.0.0
	 */
	private function save_new_option() {
		
		// Make sure we pass muster
		if (empty($_REQUEST['new_meta_key']) || empty($_REQUEST['new_meta_label']) || empty($_REQUEST['new_field_type']) || empty($_REQUEST['post_types'])) {
			$this->could_not_save_bad_fields();
			return FALSE;
		}
		
		// Grab the DB object and any helpers
		global $wpdb;
		
		// Build the insert statement
		$args = array(
			$_REQUEST['new_meta_key'],
			$_REQUEST['new_meta_label'],
			$_REQUEST['new_field_type'],
			implode(', ', $_REQUEST['post_types'])
		);
		$sql = "INSERT INTO {$wpdb->prefix}bulk_edit (
					meta_key,
					meta_label,
					field_type,
					post_types
				) VALUES (
					%s,
					%s,
					%s,
					%s
				)";
		$result = $wpdb->query($wpdb->prepare($sql,$args));	
		
		// Check for a successful add, and let the user know
		if (!empty($result)) {
			$this->saved_new_fields();
			return TRUE;
		}	
		
		// When all else fails, panic
		$this->something_went_awry();
		return FALSE;	
	}
	
	/**
	 * Deletes an editor set
	 * 
	 * @since 1.0.0
	 */
	private function delete_option() {
		
		// Make sure we pass muster
		if (empty($_REQUEST['delete'])) {
			$this->could_not_detele();
			return FALSE;
		}
			
		global $wpdb;
		$sql = "DELETE FROM {$wpdb->prefix}bulk_edit WHERE meta_key = %s";
		$result = $wpdb->query($wpdb->prepare($sql, array($_REQUEST['delete'])));
		
		if (!empty($result)) {
			$this->deteled_meta_key($_REQUEST['delete']);
			return TRUE;
		}
		
		$this->could_not_detele($_REQUEST['delete']);
		return FALSE;
	}
	
	
	
	/**
	 * Allows for bulk editing of meta info, on a single page
	 * 
	 * @since 1.0.0
	 */
	public function bulk_edit_meta() {
			
		// Grab the helpers	
		global $wpdb;
		$key = str_replace('bulk_edit_meta_','',$_REQUEST['page']);
		$base_uri = 'admin.php?page='.$_REQUEST['page'];
		
		// Check for actions				
		if (!empty($_REQUEST['edit_meta'])) {
			$this->save_bulk_edit();
		}
		
		// Check if we can edit this
		$sql = "SELECT * FROM {$wpdb->prefix}bulk_edit WHERE meta_key = %s";
		$args = array($key);
		$bulk = $wpdb->get_results($wpdb->prepare($sql,$args));
		
		// If there's a problem, chances are the user's been
		// manipulating URLs. Just give them a generic message
		if (empty($bulk)) {
			$this->something_went_awry();
			return FALSE;
		}
		
		?>
		<div class="wrap">
			<link rel='stylesheet' id='editor-buttons-css'  href='/wp-includes/css/editor.min.css?ver=4.5.6' type='text/css' media='all' />
			<h1>Editing <?php echo $bulk[0]->meta_label ?></h1>
			<form method="post" action="<?php echo $base_uri ?>">
				<fieldset>
					<?php
					
					// Add the meta key to the arguments
					$args = array(
						$bulk[0]->meta_key
					);
					
					// Add any post types
					if (strpos($bulk[0]->post_types,',') !== FALSE) {
						$exploded = explode(',',str_replace(' ','',$bulk[0]->post_types));
						$args = array_merge($args,$exploded);
					} else {
						$args[] = $bulk[0]->post_types;
					}
		
					// Grab the posts we'd like to edit
					$sql = "SELECT 
								post.ID AS post_id,
								post.post_title,
								post.post_name,
								post.post_status,
								post.post_type,
								meta.*
							FROM {$wpdb->prefix}posts AS post
								INNER JOIN {$wpdb->prefix}postmeta
									AS meta
									ON meta.post_id = post.ID
									AND meta.meta_key = %s
							WHERE post.post_type IN (".ltrim(str_repeat(',%s', (count($args)-1)),',').")
							AND post.post_status <> 'trash'
							ORDER BY post_title ASC";
					$bulk_posts = $wpdb->get_results($wpdb->prepare($sql,$args));
					
					foreach ($bulk_posts AS $bulk_post) {
						$info_list = 
							'<ul>
								<li><strong>Slug: </strong>'.$bulk_post->post_name.'</li>
								<li><strong>Status: </strong>'.ucfirst($bulk_post->post_status).'</li>	
								<li><strong>Type: </strong>'.ucwords(str_replace(array('-','_'),' ',$bulk_post->post_type)).'</li>	
								<li>
									<a class="button" target="_blank" href="'.get_permalink($bulk_post->post_id).'">View</a>
									<a class="button" target="_blank" href="post.php?post='.$bulk_post->post_id.'&action=edit">Edit</a>
								</li>				
							</ul>';
						?>
						<div class="bulk-postmeta-group">
							<h3><?php echo $bulk_post->post_title ?></h3>
							
							<?php
							switch($bulk[0]->field_type) {
								case "Single of Text":
									?>
									<input type="text" 
										id="edit_meta[<?php echo $bulk_post->meta_id ?>]" 
										name="edit_meta[<?php echo $bulk_post->meta_id ?>]" 
										value="<?php echo $bulk_post->meta_value ?>"
										class="bulk-postmeta-input" />
									<?php echo $info_list ?>
									<?php
									break;
								case "Block of Text":
									?>
									<div class="bulk-postmeta-editor">
										<textarea 
											id="edit_meta[<?php echo $bulk_post->meta_id ?>]" 
											name="edit_meta[<?php echo $bulk_post->meta_id ?>]" >
											<?php echo $bulk_post->meta_value ?>
										</textarea>
									</div>
									<div class="bulk-postmeta-list">
										<?php echo $info_list ?>
									</div>
									<?php
									break;
								case "Basic HTML":
									?>
									<div class="bulk-postmeta-editor">
									<?php
									wp_editor(
										$bulk_post->meta_value, 
										"edit_meta_{$bulk_post->meta_id}", 
										array(
											'media_buttons' => FALSE,
											'teeny' => TRUE,
											'textarea_name' => "edit_meta[{$bulk_post->meta_id}]",
											'editor_height' => 180
										) 
									);
									?>
									</div>
									<div class="bulk-postmeta-list">
										<?php echo $info_list ?>
									</div>
									<?php
									break;
								case "Full HTML with Media":
									?>
									<div class="bulk-postmeta-editor">
									<?php
									wp_editor(
										$bulk_post->meta_value, 
										"edit_meta_{$bulk_post->meta_id}", 
										array(
											'media_buttons' => TRUE,
											'teeny' => FALSE,
											'textarea_name' => "edit_meta[{$bulk_post->meta_id}]",
											'editor_height' => 180
										) 
									);
									?>
									</div>
									<div class="bulk-postmeta-list">
										<?php echo $info_list ?>
									</div>
									<?php
									break;						
							}
							?>
							<div class="clear clearfix"></div>
						</div>
						<?php
					}
					submit_button('Save Bulk Updates');
					?>
				</fieldset>
			</form>
		</div>
		<?php
	}
	
	private function save_bulk_edit() {
		
		// Send an error, if we don't have any updates
		if (empty($_REQUEST['edit_meta'])) {
			$this->could_not_save_no_updates();
			return FALSE;
		}
		
		// Include the database
		global $wpdb;
		
		// Build the base SQL
		$sql = "UPDATE {$wpdb->prefix}postmeta
				SET meta_value = %s
				WHERE meta_id = %d";
				
		// Grab the edits, and set the update flag
		$edits = $_REQUEST['edit_meta'];
		$udpated = 0;
		
		// Iterate through the updates, and try and, err, update?
		foreach ($edits AS $edit_key => $edit_value) {
			$args = array(
				$edit_value,
				$edit_key
			);	
			$results = 
				$wpdb->update(
					"{$wpdb->prefix}postmeta", 
					array(
						'meta_value' => $edit_value
					), 
					array(
						'meta_id' => $edit_key
					)
				);	
			if (!empty($results)) {
				$udpated++;
			}
		}
		
		if ($udpated) {
			$this->saved_bulk_updates($udpated);
			return TRUE;
		}
		
		$this->could_not_save_no_updates();
		return FALSE;
	}
	
	/**
	 * Prints a message, if the editor set could not be saved because of missing information
	 * 
	 * @since 1.0.0
	 */
	private function could_not_save_bad_fields() {
		?>
		<div class="notice notice-error is-dismissible">
			<p><?php echo __('Could not add new bulk editor set - some fields were missing'); ?></p>
		</div>
		<?php
	}
	
	/**
	 * Prints a message, editor could not be deleted
	 * 
	 * @since 1.0.0
	 */
	private function deteled_meta_key($meta_key = '') {
		?>
		<div class="notice notice-success is-dismissible">
			<p><?php echo __('Successfully deleted the editor set'.(strlen($meta_key) ? ", <code>{$meta_key}</code>" : '').'.'); ?></p>
		</div>
		<?php
	}
	
	/**
	 * Prints a message, editor could not be deleted
	 * 
	 * @since 1.0.0
	 */
	private function could_not_detele($meta_key = '') {
		?>
		<div class="notice notice-error is-dismissible">
			<p><?php echo __('Could not delete the editor'.(strlen($meta_key) ? ", <code>{$meta_key}</code>" : '').'. Sorry.'); ?></p>
		</div>
		<?php
	}
	
	/**
	 * Prints a message, letting the user know a new field set has been created
	 * 
	 * @since 1.0.0
	 */
	private function saved_new_fields() {
		?>
		<div class="notice notice-success is-dismissible">
			<p><?php echo __('New bulk editor field set added'); ?></p>
		</div>
		<?php
	}
	
	/**
	 * Prints a message, letting the user know a something's gone awry
	 * 
	 * @since 1.0.0
	 */
	private function something_went_awry() {
		?>
		<div class="notice notice-warning is-dismissible">
			<p><?php echo __('Whoops! Something has gone awry, sorry. Please try again.'); ?></p>
		</div>
		<?php
	}
	
	/**
	 * Prints a message, letting the user know a something's gone awry
	 * 
	 * @since 1.0.0
	 */
	private function no_fields() {
		?>
		<div class="notice notice-error">
			<p><?php echo __('No fields have been set up, yet. '.(current_user_can('activate_plugins') ? 'Please visit the <a href="admin.php?page=bulk_edit_admin_settings">settings</a> page to get started.' : '')); ?></p>
		</div>
		<?php
	}	
	
	/**
	 * Prints a message, if the the bulk editor screen could not find anything to edit
	 * 
	 * @since 1.0.0
	 */
	private function could_not_save_no_updates() {
		?>
		<div class="notice notice-error is-dismissible">
			<p><?php echo __('Could not find any updates to make'); ?></p>
		</div>
		<?php
	}
	
	/**
	 * Prints a message, letting the user know fields have been updated
	 * 
	 * @since 1.0.0
	 */
	private function saved_bulk_updates($count) {
		?>
		<div class="notice notice-success is-dismissible">
			<p><?php echo __('Updated '.$count.' field'.($count != 1 ? 's' : '')); ?></p>
		</div>
		<?php
	}
}
