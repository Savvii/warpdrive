<?php
/** 
* Get the sizes of all tables in the Database
* and display them
* @author Matthias <matthias@savvii.com>
*/

namespace Savvii;

class DatabaseSizePlugin {
	var $database_size;

	function __construct() {
		$this->database_size = new DatabaseSize();
		add_action( 'admin_menu', [ $this, 'admin_menu_init' ]);
		add_action( 'admin_bar_menu', [ $this, 'admin_bar_menu' ], 90);
	}

	function admin_menu_init() {
		add_submenu_page(
			'warpdrive_dashboard',
			'View database size',
			'View database size',
			'manage_options',
			'warpdrive_viewdatabasesize',
			[ $this, 'viewdatabasesize_page']
		);
	}

	function admin_bar_menu() {
		global $wp_admin_bar;

		if ( is_super_admin() ) {
			$wp_admin_bar->add_menu([
				'parent' => 'warpdrive_top_menu',
				'id' => 'warpdrive_viewdatabasesize',
				'title' => 'View database size',
				'href' => wp_nonce_url( admin_url( 'admin.php?page=warpdrive_viewdatabasesize' ), 'warpdrive_viewdatabasesize' ),
			]);
		}
	}

	function viewdatabasesize_page() {
		global $wpdb;

		check_admin_referer( 'warpdrive_viewdatabasesize' );
		$systemname = Options::system_name();

		$results = $wpdb->get_results( $wpdb->prepare( "
			SELECT 
     		table_schema as `database`, 
     		table_name AS `table`, 
     		round(((data_length + index_length) / 1024 / 1024), 2) `size` 
			FROM information_schema.TABLES
			WHERE table_schema = %s
			ORDER BY (data_length + index_length) DESC;", $systemname ));
		?>
		<h2>View database table sizes</h2>
		<table>
			<tr>
				<th>Database</th>
				<th>Table</th>
				<th>Size (MB)</th>
			</tr>
		<?php 
		foreach($results as $row)
		{
			var_dump($row);
			// 			echo "<td>" . $row['database'] . "</td>";
			// echo "<td>" . $row['table']    . "</td>";
			// echo "<td>" . $row['size']     . "</td>";
			// echo "</tr>";
		?>
			<tr><?php echo $row['database'] ?></tr>
		<?php
		}
		?>
		</table>
		<?php
	}	
}