<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
get_header();
?>
<div class="books-manager-single">
    <?php if ( ! is_user_logged_in() ) : ?>
        <?php echo books_manager_logged_out_message(); ?>
    <?php else : ?>
        <?php while ( have_posts() ) : the_post(); ?>
            <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
                <div class="books-manager-hero">
                    <p class="books-manager-message">
                        <a class="books-manager-button-secondary" href="<?php echo esc_url( get_post_type_archive_link( BOOKS_MANAGER_POST_TYPE ) ); ?>">
                            <?php esc_html_e( 'Back to Books', 'books-manager' ); ?>
                        </a>
                    </p>
                    <h1><?php the_title(); ?></h1>
                    <p class="books-manager-list-intro">
                        <?php esc_html_e( 'Explore the full details of this title, including author, genre, and published date.', 'books-manager' ); ?>
                    </p>
                </div>
                <div class="books-manager-book-meta">
                    <?php if ( $author = get_post_meta( get_the_ID(), '_books_manager_author', true ) ) : ?>
                        <span>
                            <span class="books-manager-meta-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
                            </span>
                            <?php printf( esc_html__( 'Author: %s', 'books-manager' ), esc_html( $author ) ); ?>
                        </span>
                    <?php endif; ?>
                    <?php if ( $genre = get_post_meta( get_the_ID(), '_books_manager_genre', true ) ) : ?>
                        <span>
                            <span class="books-manager-meta-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M20 8.69V4a1 1 0 0 0-1-1h-4.69a2 2 0 0 0-1.41.59L4.59 12.89a2 2 0 0 0 0 2.82l4.69 4.69a2 2 0 0 0 2.82 0l8.31-8.31a2 2 0 0 0 .59-1.41zM7.5 11a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3z"/></svg>
                            </span>
                            <?php printf( esc_html__( 'Genre: %s', 'books-manager' ), esc_html( $genre ) ); ?>
                        </span>
                    <?php endif; ?>
                    <?php if ( $published_date = get_post_meta( get_the_ID(), '_books_manager_published_date', true ) ) : ?>
                        <span>
                            <span class="books-manager-meta-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M19 4h-1V2h-2v2H8V2H6v2H5c-1.1 0-1.99.9-1.99 2L3 20a2 2 0 0 0 2 2h14c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 16H5V9h14v11zm-7-9h5v5h-5z"/></svg>
                            </span>
                            <?php printf( esc_html__( 'Published: %s', 'books-manager' ), esc_html( date_i18n( get_option( 'date_format' ), strtotime( $published_date ) ) ) ); ?>
                        </span>
                    <?php endif; ?>
                </div>
                <div class="books-manager-content">
                    <div class="books-manager-book-description">
                        <?php the_content(); ?>
                    </div>
                </div>
            </article>
        <?php endwhile; ?>
    <?php endif; ?>
</div>
<?php get_footer();
