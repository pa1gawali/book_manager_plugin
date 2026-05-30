<?php
/**
 * Plugin Name: Books Manager
 * Plugin URI:  https://example.com/books-manager
 * Description: Adds a Books custom post type, front-end listing shortcode, and logged-in-only access control.
 * Version:     1.0.0
 * Author:      Tie Project
 * Text Domain: books-manager
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'BOOKS_MANAGER_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'BOOKS_MANAGER_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'BOOKS_MANAGER_POST_TYPE', 'book' );

add_action( 'init', 'books_manager_register_cpt' );
add_action( 'add_meta_boxes', 'books_manager_add_meta_boxes' );
add_action( 'save_post', 'books_manager_save_meta_boxes' );
add_action( 'wp_enqueue_scripts', 'books_manager_enqueue_assets' );
add_action( 'template_include', 'books_manager_load_templates' );
add_action( 'template_redirect', 'books_manager_restrict_book_access' );
add_action( 'wp_ajax_books_manager_filter_books', 'books_manager_ajax_filter_books' );
add_filter( 'manage_edit-book_columns', 'books_manager_set_custom_book_columns' );
add_action( 'manage_book_posts_custom_column', 'books_manager_custom_book_column', 10, 2 );
add_filter( 'manage_edit-book_sortable_columns', 'books_manager_sortable_book_columns' );
add_action( 'pre_get_posts', 'books_manager_apply_sortable_book_columns' );
add_shortcode( 'books_list', 'books_manager_books_list_shortcode' );
register_activation_hook( __FILE__, 'books_manager_activate' );

/**
 * Register the Books custom post type.
 *
 * Uses: register_post_type()
 */
function books_manager_register_cpt() {
    $labels = array(
        'name'               => __( 'Books', 'books-manager' ),
        'singular_name'      => __( 'Book', 'books-manager' ),
        'menu_name'          => __( 'Books', 'books-manager' ),
        'name_admin_bar'     => __( 'Book', 'books-manager' ),
        'add_new'            => __( 'Add New', 'books-manager' ),
        'add_new_item'       => __( 'Add New Book', 'books-manager' ),
        'new_item'           => __( 'New Book', 'books-manager' ),
        'edit_item'          => __( 'Edit Book', 'books-manager' ),
        'view_item'          => __( 'View Book', 'books-manager' ),
        'all_items'          => __( 'All Books', 'books-manager' ),
        'search_items'       => __( 'Search Books', 'books-manager' ),
        'parent_item_colon'  => __( 'Parent Books:', 'books-manager' ),
        'not_found'          => __( 'No books found.', 'books-manager' ),
        'not_found_in_trash' => __( 'No books found in Trash.', 'books-manager' ),
    );

    $args = array(
        'labels'             => $labels,
        'public'             => true,
        'has_archive'        => true,
        'rewrite'            => array( 'slug' => 'books' ),
        'supports'           => array( 'title', 'editor', 'excerpt', 'page-attributes' ),
        'show_in_rest'       => true,
        'menu_position'      => 20,
        'menu_icon'          => 'dashicons-book-alt',
        'capability_type'    => 'post',
    );

    register_post_type( BOOKS_MANAGER_POST_TYPE, $args );
}

/**
 * Return the list of valid genre options for book metadata.
 *
 * @return array<string>
 */
function books_manager_get_genre_options() {
    return array(
        'Fiction',
        'Non-Fiction',
        'Sci-Fi',
        'Fantasy',
        'Mystery',
        'Romance',
        'Biography',
        'History',
    );
}

/**
 * Add custom columns to the Books admin list table.
 *
 * @param array<string, string> $columns Existing columns.
 * @return array<string, string> Modified columns.
 */
function books_manager_set_custom_book_columns( $columns ) {
    $new_columns = array();

    foreach ( $columns as $key => $value ) {
        $new_columns[ $key ] = $value;

        if ( 'title' === $key ) {
            $new_columns['books_author'] = __( 'Author', 'books-manager' );
            $new_columns['books_genre'] = __( 'Genre', 'books-manager' );
            $new_columns['books_published_date'] = __( 'Published Date', 'books-manager' );
        }
    }

    return $new_columns;
}

/**
 * Render values for custom admin columns in the Books list table.
 *
 * Uses: get_post_meta(), esc_html(), date_i18n(), get_option(), esc_html__()
 *
 * @param string $column  Column key.
 * @param int    $post_id Post ID.
 */
function books_manager_custom_book_column( $column, $post_id ) {
    switch ( $column ) {
        case 'books_author':
            $author = get_post_meta( $post_id, '_books_manager_author', true );
            echo esc_html( $author ? $author : '-' );
            break;
        case 'books_genre':
            $genre = get_post_meta( $post_id, '_books_manager_genre', true );
            echo esc_html( $genre ? $genre : '-' );
            break;
        case 'books_published_date':
            $published_date = get_post_meta( $post_id, '_books_manager_published_date', true );
            if ( $published_date ) {
                echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $published_date ) ) );
            } else {
                echo esc_html__( '-', 'books-manager' );
            }
            break;
    }
}

/**
 * Make Books admin columns sortable.
 *
 * @param array<string, string> $columns Existing sortable columns.
 * @return array<string, string> Modified sortable columns.
 */
function books_manager_sortable_book_columns( $columns ) {
    $columns['books_author'] = 'books_author';
    $columns['books_genre'] = 'books_genre';
    $columns['books_published_date'] = 'books_published_date';
    return $columns;
}

/**
 * Apply sortable behavior to Books admin queries.
 *
 * Uses: is_admin(), WP_Query methods
 *
 * @param WP_Query $query Main query instance.
 */
function books_manager_apply_sortable_book_columns( $query ) {
    if ( ! is_admin() || ! $query->is_main_query() ) {
        return;
    }

    $orderby = $query->get( 'orderby' );

    if ( 'books_author' === $orderby ) {
        $query->set( 'meta_key', '_books_manager_author' );
        $query->set( 'orderby', 'meta_value' );
    }

    if ( 'books_genre' === $orderby ) {
        $query->set( 'meta_key', '_books_manager_genre' );
        $query->set( 'orderby', 'meta_value' );
    }

    if ( 'books_published_date' === $orderby ) {
        $query->set( 'meta_key', '_books_manager_published_date' );
        $query->set( 'orderby', 'meta_value' );
    }
}

/**
 * Register the book details meta box on the Books post type screen.
 *
 * Uses: add_meta_box()
 */
function books_manager_add_meta_boxes() {
    add_meta_box(
        'books-manager-details',
        __( 'Book Details', 'books-manager' ),
        'books_manager_render_meta_box',
        BOOKS_MANAGER_POST_TYPE,
        'normal',
        'high'
    );
}

/**
 * Render the Book Details meta box.
 *
 * Uses: wp_nonce_field(), get_post_meta(), esc_html_e(), esc_attr(), selected()
 *
 * @param WP_Post $post Current post object.
 */
function books_manager_render_meta_box( $post ) {
    wp_nonce_field( 'books_manager_save_meta', 'books_manager_nonce' );

    $author = get_post_meta( $post->ID, '_books_manager_author', true );
    $genre = get_post_meta( $post->ID, '_books_manager_genre', true );
    $published_date = get_post_meta( $post->ID, '_books_manager_published_date', true );
    $genres = books_manager_get_genre_options();
    ?>
    <p>
        <label for="books-manager-author"><?php esc_html_e( 'Author', 'books-manager' ); ?></label><br>
        <input type="text" id="books-manager-author" name="books_manager_author" value="<?php echo esc_attr( $author ); ?>" class="widefat" />
    </p>
    <p>
        <label for="books-manager-genre"><?php esc_html_e( 'Genre', 'books-manager' ); ?></label><br>
        <select id="books-manager-genre" name="books_manager_genre" class="widefat">
            <option value=""><?php esc_html_e( 'Select a genre', 'books-manager' ); ?></option>
            <?php foreach ( $genres as $option ) : ?>
                <option value="<?php echo esc_attr( $option ); ?>" <?php selected( $genre, $option ); ?>><?php echo esc_html( $option ); ?></option>
            <?php endforeach; ?>
        </select>
    </p>
    <p>
        <label for="books-manager-published-date"><?php esc_html_e( 'Published Date', 'books-manager' ); ?></label><br>
        <input type="date" id="books-manager-published-date" name="books_manager_published_date" value="<?php echo esc_attr( $published_date ); ?>" class="widefat" />
    </p>
    <p class="description">
        <?php esc_html_e( 'Description is stored in the main post editor.', 'books-manager' ); ?>
    </p>
    <?php
}

/**
 * Save book metadata when the post is saved.
 *
 * Uses: wp_verify_nonce(), current_user_can(), get_post_type(), update_post_meta(), delete_post_meta()
 *
 * @param int $post_id Post ID.
 */
function books_manager_save_meta_boxes( $post_id ) {
    if ( ! isset( $_POST['books_manager_nonce'] ) || ! wp_verify_nonce( $_POST['books_manager_nonce'], 'books_manager_save_meta' ) ) {
        return;
    }

    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }

    if ( ! current_user_can( 'edit_post', $post_id ) ) {
        return;
    }

    if ( BOOKS_MANAGER_POST_TYPE !== get_post_type( $post_id ) ) {
        return;
    }

    $author = isset( $_POST['books_manager_author'] ) ? books_manager_validate_author( $_POST['books_manager_author'] ) : '';
    update_post_meta( $post_id, '_books_manager_author', $author );

    $genre = isset( $_POST['books_manager_genre'] ) ? books_manager_validate_genre( $_POST['books_manager_genre'] ) : '';
    if ( $genre ) {
        update_post_meta( $post_id, '_books_manager_genre', $genre );
    } else {
        delete_post_meta( $post_id, '_books_manager_genre' );
    }

    $published_date = isset( $_POST['books_manager_published_date'] ) ? books_manager_validate_published_date( $_POST['books_manager_published_date'] ) : '';
    if ( $published_date ) {
        update_post_meta( $post_id, '_books_manager_published_date', $published_date );
    } else {
        delete_post_meta( $post_id, '_books_manager_published_date' );
    }
}

/**
 * Sanitize and normalize author text values.
 *
 * Uses: sanitize_text_field(), wp_unslash(), mb_substr()
 *
 * @param string $author Raw author input.
 * @return string Sanitized author.
 */
function books_manager_validate_author( $author ) {
    $author = sanitize_text_field( wp_unslash( $author ) );
    $author = trim( $author );
    return mb_substr( $author, 0, 120 );
}

/**
 * Validate the selected genre against the allowed genre list.
 *
 * Uses: sanitize_text_field(), wp_unslash(), books_manager_get_genre_options()
 *
 * @param string $genre Raw genre input.
 * @return string Validated genre or empty string.
 */
function books_manager_validate_genre( $genre ) {
    $genre = sanitize_text_field( wp_unslash( $genre ) );
    $allowed_genres = books_manager_get_genre_options();
    return in_array( $genre, $allowed_genres, true ) ? $genre : '';
}

/**
 * Validate the published date value.
 *
 * Uses: sanitize_text_field(), wp_unslash(), preg_match()
 *
 * @param string $date Raw date input.
 * @return string Validated date in YYYY-MM-DD format or empty string.
 */
function books_manager_validate_published_date( $date ) {
    $date = sanitize_text_field( wp_unslash( $date ) );
    return preg_match( '/^\d{4}-\d{2}-\d{2}$/', $date ) ? $date : '';
}

/**
 * Normalize page number inputs for pagination.
 *
 * Uses: absint()
 *
 * @param mixed $page Raw page value.
 * @return int Valid page number.
 */
function books_manager_validate_page_number( $page ) {
    $page = absint( $page );
    return $page > 0 ? $page : 1;
}

/**
 * Enqueue plugin styles and scripts on the front end.
 *
 * Uses: is_admin(), wp_enqueue_style(), wp_enqueue_script(), wp_localize_script(), admin_url(), wp_create_nonce()
 */
function books_manager_enqueue_assets() {
    if ( is_admin() ) {
        return;
    }

    wp_enqueue_style(
        'books-manager-styles',
        BOOKS_MANAGER_PLUGIN_URL . 'assets/css/books-manager.css',
        array(),
        '1.0.0'
    );

    wp_enqueue_script(
        'books-manager-js',
        BOOKS_MANAGER_PLUGIN_URL . 'assets/js/books-manager.js',
        array( 'jquery' ),
        '1.0.0',
        true
    );

    wp_localize_script(
        'books-manager-js',
        'booksManager',
        array(
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce'    => wp_create_nonce( 'books_manager_filter' ),
        )
    );
}

/**
 * Load plugin template files for Books post type views.
 *
 * Uses: is_singular(), is_post_type_archive(), file_exists()
 *
 * @param string $template Current template file.
 * @return string Template path.
 */
function books_manager_load_templates( $template ) {
    if ( is_singular( BOOKS_MANAGER_POST_TYPE ) ) {
        $custom_template = BOOKS_MANAGER_PLUGIN_DIR . 'templates/single-book.php';
        if ( file_exists( $custom_template ) ) {
            return $custom_template;
        }
    }

    if ( is_post_type_archive( BOOKS_MANAGER_POST_TYPE ) ) {
        $custom_template = BOOKS_MANAGER_PLUGIN_DIR . 'templates/archive-book.php';
        if ( file_exists( $custom_template ) ) {
            return $custom_template;
        }
    }

    return $template;
}

/**
 * Restrict direct access to Books archive and single views for logged-out users.
 *
 * Uses: is_singular(), is_post_type_archive(), is_user_logged_in(), auth_redirect()
 */
function books_manager_restrict_book_access() {
    if ( is_singular( BOOKS_MANAGER_POST_TYPE ) || is_post_type_archive( BOOKS_MANAGER_POST_TYPE ) ) {
        if ( ! is_user_logged_in() ) {
            auth_redirect();
        }
    }
}

/**
 * Render the books listing shortcode content for logged-in users.
 *
 * Uses: is_user_logged_in(), books_manager_logged_out_message(), books_manager_validate_page_number(), books_manager_validate_genre(), books_manager_validate_author()
 *
 * @param array<string, mixed> $atts Shortcode attributes.
 * @return string HTML output.
 */
function books_manager_books_list_shortcode( $atts ) {
    if ( ! is_user_logged_in() ) {
        return books_manager_logged_out_message();
    }

    $filters = array(
        'paged'  => isset( $_GET['books_page'] ) ? books_manager_validate_page_number( $_GET['books_page'] ) : 1,
        'genre'  => isset( $_GET['books_genre'] ) ? books_manager_validate_genre( $_GET['books_genre'] ) : '',
        'author' => isset( $_GET['books_author'] ) ? books_manager_validate_author( $_GET['books_author'] ) : '',
    );

    return books_manager_render_book_list( $filters );
}

/**
 * Render the book list markup from the shortcode template.
 *
 * Uses: wp_parse_args(), ob_start(), ob_get_clean()
 *
 * @param array<string, mixed> $args Query and filter arguments.
 * @return string HTML output.
 */
function books_manager_render_book_list( $args = array() ) {
    $args = wp_parse_args(
        $args,
        array(
            'paged'  => 1,
            'genre'  => '',
            'author' => '',
        )
    );

    $books_query = books_manager_get_books_query( $args );
    ob_start();
    include BOOKS_MANAGER_PLUGIN_DIR . 'templates/shortcode-books-list.php';
    return ob_get_clean();
}

/**
 * Build a WP_Query instance for book listings.
 *
 * Uses: WP_Query
 *
 * @param array<string, mixed> $args Query and filter arguments.
 * @return WP_Query Prepared books query.
 */
function books_manager_get_books_query( $args = array() ) {
    $meta_query = array();

    if ( ! empty( $args['genre'] ) ) {
        $genre = books_manager_validate_genre( $args['genre'] );
        if ( $genre ) {
            $meta_query[] = array(
                'key'     => '_books_manager_genre',
                'value'   => $genre,
                'compare' => '=',
            );
        }
    }

    if ( ! empty( $args['author'] ) ) {
        $author = books_manager_validate_author( $args['author'] );
        if ( $author ) {
            $meta_query[] = array(
                'key'     => '_books_manager_author',
                'value'   => $author,
                'compare' => 'LIKE',
            );
        }
    }

    $query_args = array(
        'post_type'      => BOOKS_MANAGER_POST_TYPE,
        'posts_per_page' => 5,
        'paged'          => absint( $args['paged'] ),
        'orderby'        => 'date',
        'order'          => 'DESC',
    );

    if ( ! empty( $meta_query ) ) {
        $query_args['meta_query'] = $meta_query;
    }

    return new WP_Query( $query_args );
}

/**
 * Handle AJAX book filtering for logged-in users.
 *
 * Uses: check_ajax_referer(), is_user_logged_in(), wp_send_json_error(), wp_send_json_success()
 */
function books_manager_ajax_filter_books() {
    check_ajax_referer( 'books_manager_filter', 'nonce' );

    if ( ! is_user_logged_in() ) {
        wp_send_json_error( array( 'message' => __( 'Please log in to view books.', 'books-manager' ) ) );
    }

    $filters = array(
        'paged'  => isset( $_POST['books_page'] ) ? books_manager_validate_page_number( $_POST['books_page'] ) : 1,
        'genre'  => isset( $_POST['books_genre'] ) ? books_manager_validate_genre( $_POST['books_genre'] ) : '',
        'author' => isset( $_POST['books_author'] ) ? books_manager_validate_author( $_POST['books_author'] ) : '',
    );

    $html = books_manager_render_book_list( $filters );
    wp_send_json_success( array( 'html' => $html ) );
}

/**
 * Display a logged-out notice for shortcode output.
 *
 * Uses: wp_login_url(), get_permalink(), wp_registration_url(), esc_html_e(), esc_url(), ob_start(), ob_get_clean()
 *
 * @return string HTML markup.
 */
function books_manager_logged_out_message() {
    $login_url    = wp_login_url( get_permalink() );
    $register_url = wp_registration_url();

    ob_start();
    ?>
    <div class="books-manager-restricted">
        <h1><?php esc_html_e( 'Restricted Content', 'books-manager' ); ?></h1>
        <p><?php esc_html_e( 'You must be logged in to view this content. Please log in or register.', 'books-manager' ); ?></p>
        <p>
            <a class="books-manager-button" href="<?php echo esc_url( $login_url ); ?>"><?php esc_html_e( 'Log In', 'books-manager' ); ?></a>
            <a class="books-manager-button books-manager-button-secondary" href="<?php echo esc_url( $register_url ); ?>"><?php esc_html_e( 'Register', 'books-manager' ); ?></a>
        </p>
    </div>
    <?php
    return ob_get_clean();
}

/**
 * Plugin activation callback.
 *
 * Uses: books_manager_register_cpt(), flush_rewrite_rules(), get_page_by_title(), wp_insert_post(), get_current_user_id()
 *
 * Register the CPT, flush rewrite rules, and create the listing page.
 */
function books_manager_activate() {
    books_manager_register_cpt();
    flush_rewrite_rules();

    $page_title = 'Books Listing';
    $page_check = get_page_by_title( $page_title );

    if ( ! $page_check ) {
        wp_insert_post(
            array(
                'post_title'   => $page_title,
                'post_content' => '[books_list]',
                'post_status'  => 'publish',
                'post_type'    => 'page',
                'post_author'  => get_current_user_id() ? get_current_user_id() : 1,
            )
        );
    }
}
