<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$genres = books_manager_get_genre_options();
$current_genre = isset( $args['genre'] ) ? $args['genre'] : '';
$current_author = isset( $args['author'] ) ? $args['author'] : '';
$paged = isset( $args['paged'] ) ? absint( $args['paged'] ) : 1;
?>
<div id="books-manager-list-wrapper" class="books-manager-list">
    <div class="books-manager-hero">
        <h1><?php esc_html_e( 'Books List', 'books-manager' ); ?></h1>
        <p class="books-manager-list-intro">
            <?php esc_html_e( 'Discover and filter the latest titles in our secure book collection. Use the author and genre filters for faster navigation.', 'books-manager' ); ?>
        </p>
    </div>

    <form id="books-manager-filter-form" class="books-manager-list-form" method="get">
        <input type="text" name="books_author" placeholder="<?php esc_attr_e( 'Filter by author', 'books-manager' ); ?>" value="<?php echo esc_attr( $current_author ); ?>" />
        <select name="books_genre">
            <option value=""><?php esc_html_e( 'All genres', 'books-manager' ); ?></option>
            <?php foreach ( $genres as $genre_option ) : ?>
                <option value="<?php echo esc_attr( $genre_option ); ?>" <?php selected( $current_genre, $genre_option ); ?>><?php echo esc_html( $genre_option ); ?></option>
            <?php endforeach; ?>
        </select>
        <input type="hidden" name="books_page" value="<?php echo esc_attr( $paged ); ?>" />
        <button type="submit" class="books-manager-button"><?php esc_html_e( 'Apply Filters', 'books-manager' ); ?></button>
    </form>

    <?php if ( $books_query->have_posts() ) : ?>
        <div class="books-manager-book-grid">
            <?php while ( $books_query->have_posts() ) : $books_query->the_post(); ?>
                <div class="books-manager-book-card">
                    <div class="books-manager-book-meta-row">
                        <?php if ( $author = get_post_meta( get_the_ID(), '_books_manager_author', true ) ) : ?>
                            <span class="books-manager-book-author">
                                <span class="books-manager-meta-icon" aria-hidden="true">
                                    <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
                                </span>
                                <?php printf( esc_html__( 'Author: %s', 'books-manager' ), esc_html( $author ) ); ?>
                            </span>
                        <?php endif; ?>
                        <?php if ( $genre = get_post_meta( get_the_ID(), '_books_manager_genre', true ) ) : ?>
                            <span class="books-manager-book-genre">
                                <span class="books-manager-meta-icon" aria-hidden="true">
                                    <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M20 8.69V4a1 1 0 0 0-1-1h-4.69a2 2 0 0 0-1.41.59L4.59 12.89a2 2 0 0 0 0 2.82l4.69 4.69a2 2 0 0 0 2.82 0l8.31-8.31a2 2 0 0 0 .59-1.41zM7.5 11a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3z"/></svg>
                                </span>
                                <?php printf( esc_html__( 'Genre: %s', 'books-manager' ), esc_html( $genre ) ); ?>
                            </span>
                        <?php endif; ?>
                        <?php if ( $published_date = get_post_meta( get_the_ID(), '_books_manager_published_date', true ) ) : ?>
                            <span class="books-manager-book-date">
                                <span class="books-manager-meta-icon" aria-hidden="true">
                                    <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M19 4h-1V2h-2v2H8V2H6v2H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2zm0 16H5V9h14v11zm-7-9h5v5h-5z"/></svg>
                                </span>
                                <?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $published_date ) ) ); ?>
                            </span>
                        <?php endif; ?>
                    </div>
                    <h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                    <div class="books-manager-book-description">
                        <?php echo wp_kses_post( wp_trim_words( get_the_content(), 30, '…' ) ); ?>
                    </div>
                    <center><p><a class="books-manager-button books-manager-button-secondary" href="<?php the_permalink(); ?>"><?php esc_html_e( 'View Details', 'books-manager' ); ?></a></p></center>
                </div>
            <?php endwhile; ?>
        </div>

        <div class="books-manager-pagination">
            <?php
            echo paginate_links( array(
                'base'      => add_query_arg( 'books_page', '%#%' ),
                'format'    => '?books_page=%#%',
                'current'   => max( 1, $paged ),
                'total'     => $books_query->max_num_pages,
                'prev_text' => '&laquo; ' . esc_html__( 'Previous', 'books-manager' ),
                'next_text' => esc_html__( 'Next', 'books-manager' ) . ' &raquo;',
            ) );
            ?>
        </div>
    <?php else : ?>
        <p><?php esc_html_e( 'No books match that filter. Try a different keyword or genre.', 'books-manager' ); ?></p>
    <?php endif; ?>

    <?php wp_reset_postdata(); ?>
</div>
