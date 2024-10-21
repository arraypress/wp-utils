<?php
/**
 * WordPress Dynamic Table Generator
 *
 * A flexible class for generating tables in WordPress with custom formatting options.
 *
 * @package     ArrayPress/WP-Utils
 * @copyright   Copyright (c) 2024, ArrayPress Limited
 * @license     GPL2+
 * @since       1.0.0
 * @author      David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\WP\Utils\Generate;

/**
 * Check if the class `Table` is defined, and if not, define it.
 */
if ( ! class_exists( 'Table' ) ) :

	/**
	 * Table Class
	 *
	 * Provides functionality to register and render dynamic tables in WordPress.
	 */
	class Table {

		/**
		 * Registered tables.
		 *
		 * @var array
		 */
		private array $tables = [];

		/**
		 * Register a new table.
		 *
		 * @param string $key  Unique identifier for the table.
		 * @param array  $args Configuration arguments for the table.
		 *
		 * @return void
		 */
		public function register( string $key, array $args ): void {
			$defaults = [
				'title'         => '',
				'columns'       => [],
				'data_callback' => '',
				'empty_message' => __( 'No items found', 'wp-utils' ),
				'table_class'   => '',
			];

			$this->tables[ $key ] = wp_parse_args( $args, $defaults );
		}

		/**
		 * Render a table.
		 *
		 * @param string     $key  The table key.
		 * @param array|null $data Optional. Data to use instead of calling the data_callback.
		 *
		 * @return void
		 */
		public function render( string $key, ?array $data = null ): void {
			if ( ! isset( $this->tables[ $key ] ) ) {
				return;
			}

			$table = $this->tables[ $key ];

			if ( $data === null && is_callable( $table['data_callback'] ) ) {
				$data = call_user_func( $table['data_callback'] );
			}

			$this->render_table( $key, $table, $data );
		}

		/**
		 * Render a single table.
		 *
		 * @param string     $key   The table key.
		 * @param array      $table The table configuration.
		 * @param array|null $data  The table data.
		 *
		 * @return void
		 */
		private function render_table( string $key, array $table, ?array $data ): void {
			$table_class = 'wp-list-table widefat striped ' . esc_attr( $table['table_class'] );
			?>
			<?php if ( ! empty( $table['title'] ) ) : ?>
                <h3><?php echo esc_html( $table['title'] ); ?></h3>
			<?php endif; ?>
            <table class="<?php echo $table_class; ?>">
                <thead>
                <tr>
					<?php foreach ( $table['columns'] as $column_key => $column ) : ?>
                        <th class="<?php echo esc_attr( $column_key ); ?> <?php echo $column_key === array_key_first( $table['columns'] ) ? 'column-primary' : ''; ?>">
							<?php echo wp_kses_post( $column['label'] ); ?>
                        </th>
					<?php endforeach; ?>
                </tr>
                </thead>
                <tbody>
				<?php if ( ! empty( $data ) ) : ?>
					<?php foreach ( $data as $row ) : ?>
                        <tr>
							<?php foreach ( $table['columns'] as $column_key => $column ) : ?>
                                <td class="<?php echo esc_attr( $column_key ); ?> <?php echo $column_key === array_key_first( $table['columns'] ) ? 'column-primary' : ''; ?>">
									<?php
									echo isset( $column['prefix'] ) ? wp_kses_post( $column['prefix'] ) : '';

									if ( isset( $column['render_callback'] ) && is_callable( $column['render_callback'] ) ) {
										echo call_user_func( $column['render_callback'], $row, $column_key );
									} elseif ( isset( $row[ $column_key ] ) ) {
										echo wp_kses_post( $row[ $column_key ] );
									} elseif ( isset( $column['fallback'] ) ) {
										echo wp_kses_post( $column['fallback'] );
									} else {
										echo '&mdash;';
									}

									echo isset( $column['suffix'] ) ? wp_kses_post( $column['suffix'] ) : '';
									?>
                                </td>
							<?php endforeach; ?>
                        </tr>
					<?php endforeach; ?>
				<?php else : ?>
                    <tr>
                        <td colspan="<?php echo esc_attr( count( $table['columns'] ) ); ?>" class="no-items">
							<?php echo esc_html( $table['empty_message'] ); ?>
                        </td>
                    </tr>
				<?php endif; ?>
                </tbody>
            </table>
			<?php
		}
	}

endif;

// Usage example:
$table_generator = new DynamicTableGenerator();

// Register a table
$table_generator->register( 'recent_orders', [
	'title'         => __( 'Recent Orders', 'easy-digital-downloads' ),
	'table_class'   => 'customer-payments',
	'columns'       => [
		'number'  => [
			'label'           => __( 'Number', 'easy-digital-downloads' ),
			'prefix'          => '<strong>',
			'suffix'          => '</strong>',
			'render_callback' => function ( $row, $column_key ) {
				$view_url = edd_get_admin_url( [
					'page' => 'edd-payment-history',
					'view' => 'view-order-details',
					'id'   => absint( $row['id'] ),
				] );
				$state    = ( 'complete' !== $row['status'] ) ? ' &mdash; ' . edd_get_payment_status_label( $row['status'] ) : '';

				return sprintf(
					'<a class="row-title" href="%s">%s</a>%s',
					esc_url( $view_url ),
					esc_html( $row['number'] ),
					esc_html( $state )
				);
			},
		],
		'gateway' => [
			'label'           => __( 'Gateway', 'easy-digital-downloads' ),
			'render_callback' => function ( $row, $column_key ) {
				return esc_html( edd_get_gateway_admin_label( $row['gateway'] ) );
			},
		],
		'total'   => [
			'label'           => __( 'Total', 'easy-digital-downloads' ),
			'render_callback' => function ( $row, $column_key ) {
				return edd_currency_filter( edd_format_amount( $row['total'] ), $row['currency'] );
			},
		],
		'date'    => [
			'label'           => __( 'Date', 'easy-digital-downloads' ),
			'prefix'          => '<time datetime="' . esc_attr( EDD()->utils->date( 'now', null, true )->toDateTimeString() ) . '">',
			'suffix'          => '</time>',
			'render_callback' => function ( $row, $column_key ) {
				return edd_date_i18n( $row['date_created'], 'M. d, Y' ) . '<br>' . edd_date_i18n( strtotime( $row['date_created'] ), 'H:i' ) . ' ' . edd_get_timezone_abbr();
			},
		],
	],
	'data_callback' => function () {
		// Fetch and return your order data here
		return []; // Placeholder
	},
	'empty_message' => __( 'No orders found', 'easy-digital-downloads' ),
] );

// Render the table
$table_generator->render( 'recent_orders' );