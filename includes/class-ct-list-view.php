<?php
/**
 * List View class
 *
 * @since 1.0.0
 */
// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'CT_List_View' ) ) :

    class CT_List_View extends CT_View {

        protected $per_page = 20;

        public function __construct($name, $args) {

            parent::__construct($name, $args);

            $this->per_page = isset( $args['per_page'] ) ? $args['per_page'] : 20;

        }

        /**
         * Screen settings text displayed in the Screen Options tab.
         *
         * @param string    $screen_settings    Screen settings.
         * @param WP_Screen $screen             WP_Screen object.
         */
        public function screen_settings( $screen_settings, $screen ) {

            $this->render_list_table_columns_preferences();
            $this->render_per_page_options();

        }

        /**
         * Render the list table columns preferences.
         *
         * @since 1.0.0
         */
        public function render_list_table_columns_preferences() {

            global $ct_table;

            // Set up vars
            $columns = apply_filters( "manage_{$ct_table->name}_columns", array() );
            $hidden  = get_hidden_columns( $ct_table->name );

            if ( ! $columns ) {
                return;
            }

            $legend = ! empty( $columns['_title'] ) ? $columns['_title'] : __( 'Columns' );
            ?>
            <fieldset class="metabox-prefs">
                <legend><?php echo $legend; ?></legend>
                <?php
                $special = array( '_title', 'cb' );

                foreach ( $columns as $column => $title ) {
                    // Can't hide these for they are special
                    if ( in_array( $column, $special ) ) {
                        continue;
                    }

                    if ( empty( $title ) ) {
                        continue;
                    }

                    $id = "$column-hide";
                    echo '<label>';
                    echo '<input class="hide-column-tog" name="' . $id . '" type="checkbox" id="' . $id . '" value="' . $column . '"' . checked( ! in_array( $column, $hidden ), true, false ) . ' />';
                    echo "$title</label>\n";
                }
                ?>
            </fieldset>
            <?php
        }

        /**
         * Render the items per page option
         *
         * @since 3.3.0
         */
        public function render_per_page_options() {

            global $ct_table;

            if ( ! $this->per_page ) {
                return;
            }

            // Set up vars
            $per_page_label = __( 'Number of items per page:' );

            $option = str_replace( '-', '_', "{$ct_table->name}_per_page" );

            $per_page = (int) get_user_option( $option );

            if ( empty( $per_page ) || $per_page < 1 ) {
                $per_page = $this->per_page;
            }

            $per_page = apply_filters( "{$option}", $per_page );

            // This needs a submit button
            add_filter( 'screen_options_show_submit', '__return_true' );

            ?>
            <fieldset class="screen-options">
                <legend><?php _e( 'Pagination' ); ?></legend>
                <?php if ( $per_page_label ) : ?>
                    <label for="<?php echo esc_attr( $option ); ?>"><?php echo $per_page_label; ?></label>
                    <input type="number" step="1" min="1" max="999" class="screen-per-page" name="wp_screen_options[value]"
                           id="<?php echo esc_attr( $option ); ?>" maxlength="3"
                           value="<?php echo esc_attr( $per_page ); ?>" />
                <?php endif; ?>
                <input type="hidden" name="wp_screen_options[option]" value="<?php echo esc_attr( $option ); ?>" />
            </fieldset>
            <?php
        }

        public function render() {

            global $ct_registered_tables, $ct_table, $ct_query, $ct_list_table;

            if( ! isset( $ct_registered_tables[$this->name] ) ) {
                return;
            }

            // Set up vars
            $ct_table = $ct_registered_tables[$this->name];
            $ct_query = new CT_Query( $_GET );
            $ct_list_table = new CT_List_Table();

            // Setup screen options
            //add_screen_option( 'per_page', array( 'default' => 20, 'option' => 'edit_' . $ct_table->name . '_per_page' ) );

            $ct_list_table->prepare_items();

            ?>

            <div class="wrap">

                <h1 class="wp-heading-inline"><?php echo $ct_table->labels->plural_name; ?></h1>

                <?php $ct_list_table->views(); ?>

                <form id="ct-list-filter" method="get">

                    <input type="hidden" name="page" value="<?php echo esc_attr( $this->args['menu_slug'] ); ?>" />

                    <?php $ct_list_table->search_box( $ct_table->labels->search_items, $ct_table->name ); ?>

                    <?php $ct_list_table->display(); ?>

                </form>

                <div id="ajax-response"></div>
                <br class="clear" />

            </div>

            <?php
        }

    }

endif;