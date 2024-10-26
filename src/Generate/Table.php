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

namespace ArrayPress\Utils\Generate;

use ArrayPress\Utils\Common\Request;
use ArrayPress\Utils\Common\Format;

/**
 * Check if the class `Table` is defined, and if not, define it.
 */
if ( ! class_exists( 'Table' ) ):

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
				'pagination'    => [
					'enabled'      => false,
					'per_page'     => 20,
					'current_page' => Request::get_current_page(),
					'total_items'  => 0,
					'base'         => remove_query_arg( 'paged' ), // Just store the clean base URL
					'format'       => '&paged=%#%', // Move the format here
					'add_args'     => [],
				],
				'row_actions'   => [], // New default for row actions
			];

			// Process columns
			if ( ! empty( $args['columns'] ) ) {
				foreach ( $args['columns'] as $column_key => $column ) {
					if ( empty( $column ) ) {
						unset( $args['columns'][ $column_key ] );
						continue;
					}

					$args['columns'][ $column_key ] = wp_parse_args( $column, [
						'label'           => Format::label( $column_key ),
						'header_class'    => '',
						'column_class'    => '',
						'prefix'          => '',
						'suffix'          => '',
						'render_callback' => null,
						'fallback'        => '',
						'is_primary'      => $column_key === array_key_first( $args['columns'] ), // New property
					] );
				}
			}

			// Merge pagination settings if provided
			if ( isset( $args['pagination'] ) && is_array( $args['pagination'] ) ) {

				// Preserve the default base URL unless explicitly overridden
				if ( empty( $args['pagination']['base'] ) ) {
					$args['pagination']['base'] = $defaults['pagination']['base'];
				}
				// Preserve the default current page unless explicitly overridden
				if ( ! isset( $args['pagination']['current_page'] ) ) {
					$args['pagination']['current_page'] = $defaults['pagination']['current_page'];
				}

				$args['pagination'] = wp_parse_args( $args['pagination'], $defaults['pagination'] );
			}

			$this->tables[ $key ] = wp_parse_args( $args, $defaults );
		}

		/**
		 * Get HTML class attribute if classes exist.
		 *
		 * @param array|string $classes Classes to process.
		 *
		 * @return string HTML class attribute or empty string.
		 */
		private function get_class_attribute( $classes ): string {
			if ( is_string( $classes ) ) {
				$classes = explode( ' ', $classes );
			}
			$classes = array_filter( $classes );

			return ! empty( $classes ) ? ' class="' . esc_attr( implode( ' ', $classes ) ) . '"' : '';
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

			// Handle pagination if enabled
			if ( $table['pagination']['enabled'] ) {
				$current_page = max( 1, $table['pagination']['current_page'] );
				$per_page     = $table['pagination']['per_page'];

				if ( $data === null && is_callable( $table['data_callback'] ) ) {
					// Calculate offset
					$offset = ( $current_page - 1 ) * $per_page;

					// Get paginated data
					$data = call_user_func( $table['data_callback'], [
						'number' => $per_page,
						'offset' => $offset
					] );
				}
			} else {
				if ( $data === null && is_callable( $table['data_callback'] ) ) {
					$data = call_user_func( $table['data_callback'] );
				}
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
			$table_classes = [ 'wp-list-table', 'widefat', 'striped' ];
			if ( ! empty( $table['table_class'] ) ) {
				$table_classes[] = $table['table_class'];
			}
			?>
			<?php if ( ! empty( $table['title'] ) ): ?>
                <h3><?php echo esc_html( $table['title'] ); ?></h3>
			<?php endif; ?>

            <table<?php echo $this->get_class_attribute( $table_classes ); ?>>
                <thead>
                <tr>
					<?php foreach ( $table['columns'] as $column_key => $column ): ?>
						<?php
						$header_classes = array_filter( [
							$column_key,
							$column['is_primary'] ? 'column-primary' : '',
							$column['header_class']
						] );
						?>
                        <th<?php echo $this->get_class_attribute( $header_classes ); ?>>
							<?php echo wp_kses_post( $column['label'] ); ?>
                        </th>
					<?php endforeach; ?>
                </tr>
                </thead>
                <tbody>
				<?php if ( ! empty( $data ) ): ?>
					<?php foreach ( $data as $row ): ?>
                        <tr>
							<?php foreach ( $table['columns'] as $column_key => $column ): ?>
								<?php
								$column_classes = array_filter( [
									$column_key,
									$column['is_primary'] ? 'column-primary' : '',
									$column['column_class']
								] );
								?>
                                <td<?php echo $this->get_class_attribute( $column_classes ); ?>>
									<?php echo $this->render_cell( $column, $column_key, $row, $table ); ?>
									<?php if ( $column['is_primary'] ): ?>
                                        <button type="button" class="toggle-row">
                                            <span class="screen-reader-text">
                                                <?php _e( 'Show more details', 'wp-utils' ); ?>
                                            </span>
                                        </button>
									<?php endif; ?>
                                </td>
							<?php endforeach; ?>
                        </tr>
					<?php endforeach; ?>
				<?php else: ?>
                    <tr>
                        <td colspan="<?php echo esc_attr( count( $table['columns'] ) ); ?>" class="no-items">
							<?php echo esc_html( $table['empty_message'] ); ?>
                        </td>
                    </tr>
				<?php endif; ?>
                </tbody>
            </table>

			<?php
			if ( isset( $table['pagination'] ) ) {
				$this->render_pagination( $table['pagination'] );
			}
		}

		/**
		 * Render a single table cell.
		 *
		 * @param array        $column     Column configuration.
		 * @param string       $column_key Column key.
		 * @param object|array $row        Row data.
		 * @param array        $table      Table configuration.
		 *
		 * @return string
		 */
		private function render_cell( array $column, string $column_key, $row, array $table ): string {
			$value = '';

			if ( isset( $column['render_callback'] ) && is_callable( $column['render_callback'] ) ) {
				$value = call_user_func( $column['render_callback'], $row, $column_key );
			} elseif ( is_array( $row ) && isset( $row[ $column_key ] ) ) {
				$value = $row[ $column_key ];
			} elseif ( is_object( $row ) && isset( $row->$column_key ) ) {
				$value = $row->$column_key;
			} elseif ( isset( $column['fallback'] ) ) {
				$value = $column['fallback'];
			}

			if ( empty( $value ) ) {
				$value = '&mdash;';
			} else {
				// Handle prefix
				$prefix = '';
				if ( isset( $column['prefix'] ) ) {
					if ( is_callable( $column['prefix'] ) ) {
						$prefix = call_user_func( $column['prefix'], $row, $column_key );
					} else {
						$prefix = $column['prefix'];
					}
				}

				// Handle suffix
				$suffix = '';
				if ( isset( $column['suffix'] ) ) {
					if ( is_callable( $column['suffix'] ) ) {
						$suffix = call_user_func( $column['suffix'], $row, $column_key );
					} else {
						$suffix = $column['suffix'];
					}
				}

				$value = wp_kses_post( $prefix ) . wp_kses_post( $value ) . wp_kses_post( $suffix );
			}

			// Add row actions if this is the primary column and we have actions
			if ( ! empty( $column['is_primary'] ) && ! empty( $table['row_actions'] ) && is_callable( $table['row_actions'] ) ) {
				$actions = call_user_func( $table['row_actions'], $row );
				if ( ! empty( $actions ) ) {
					$value .= $this->get_row_actions( $actions, $row );
				}
			}

			return $value;
		}

		/**
		 * Get row actions HTML.
		 *
		 * @param array        $actions Array of actions.
		 * @param object|array $item    Current item being displayed.
		 *
		 * @return string
		 */
		private function get_row_actions( array $actions, $item ): string {
			if ( empty( $actions ) ) {
				return '';
			}

			$output = '<div class="row-actions">';
			$i      = 0;
			$count  = count( $actions );

			foreach ( $actions as $action => $link ) {
				++ $i;
				$sep    = ( $i < $count ) ? ' | ' : '';
				$output .= "<span class='$action'>$link$sep</span>";
			}
			$output .= '</div>';

			return $output;
		}

		/**
		 * Render pagination links.
		 *
		 * @param array $pagination Pagination configuration.
		 *
		 * @return void
		 */
		private function render_pagination( array $pagination ): void {
			if ( ! $pagination['enabled'] || $pagination['total_items'] <= $pagination['per_page'] ) {
				return;
			}

			$total_pages = ceil( $pagination['total_items'] / $pagination['per_page'] );
			if ( $total_pages <= 1 ) {
				return;
			}

			$current     = $pagination['current_page'];
			$current_url = $pagination['base'];
			$page_links  = [];

			// Output total items text
			$output = '<span class="displaying-num">' . sprintf(
					_n( '%s item', '%s items', $pagination['total_items'], 'wp-utils' ),
					number_format_i18n( $pagination['total_items'] )
				) . '</span>';

			// First page
			if ( $current === 1 ) {
				$page_links[] = '<span class="tablenav-pages-navspan button disabled" aria-hidden="true">&laquo;</span>';
			} else {
				$page_links[] = sprintf(
					'<a class="first-page button" href="%s">' .
					'<span class="screen-reader-text">%s</span>' .
					'<span aria-hidden="true">%s</span>' .
					'</a>',
					esc_url( remove_query_arg( 'paged', $current_url ) ),
					__( 'First page', 'wp-utils' ),
					'&laquo;'
				);
			}

			// Previous
			if ( $current === 1 ) {
				$page_links[] = '<span class="tablenav-pages-navspan button disabled" aria-hidden="true">&lsaquo;</span>';
			} else {
				$page_links[] = sprintf(
					'<a class="prev-page button" href="%s">' .
					'<span class="screen-reader-text">%s</span>' .
					'<span aria-hidden="true">%s</span>' .
					'</a>',
					esc_url( add_query_arg( 'paged', max( 1, $current - 1 ), $current_url ) ),
					__( 'Previous page', 'wp-utils' ),
					'&lsaquo;'
				);
			}

			// Current page and total pages
			$page_links[] = sprintf(
				'<span class="paging-input">' .
				'%s of <span class="total-pages">%s</span>' .
				'</span>',
				$current,
				number_format_i18n( $total_pages )
			);

			// Next
			if ( $current >= $total_pages ) {
				$page_links[] = '<span class="tablenav-pages-navspan button disabled" aria-hidden="true">&rsaquo;</span>';
			} else {
				$page_links[] = sprintf(
					'<a class="next-page button" href="%s">' .
					'<span class="screen-reader-text">%s</span>' .
					'<span aria-hidden="true">%s</span>' .
					'</a>',
					esc_url( add_query_arg( 'paged', min( $total_pages, $current + 1 ), $current_url ) ),
					__( 'Next page', 'wp-utils' ),
					'&rsaquo;'
				);
			}

			// Last page
			if ( $current >= $total_pages ) {
				$page_links[] = '<span class="tablenav-pages-navspan button disabled" aria-hidden="true">&raquo;</span>';
			} else {
				$page_links[] = sprintf(
					'<a class="last-page button" href="%s">' .
					'<span class="screen-reader-text">%s</span>' .
					'<span aria-hidden="true">%s</span>' .
					'</a>',
					esc_url( add_query_arg( 'paged', $total_pages, $current_url ) ),
					__( 'Last page', 'wp-utils' ),
					'&raquo;'
				);
			}

			$output .= "\n<span class='pagination-links'>" . implode( "\n", $page_links ) . '</span>';

			$page_class = $total_pages < 2 ? ' one-page' : '';

			echo "<div class='tablenav'><div class='tablenav-pages{$page_class}'>$output</div></div>";
		}

	}

endif;