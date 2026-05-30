<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
get_header();
?>
<div class="books-manager-list">
    <?php if ( ! is_user_logged_in() ) : ?>
        <?php echo books_manager_logged_out_message(); ?>
    <?php else : ?>
        <div class="books-manager-hero">
            <h1><?php esc_html_e( 'Book Collection', 'books-manager' ); ?></h1>
            <p class="books-manager-list-intro">
                <?php esc_html_e( 'Browse our curated book library with filters for author and genre. Login to unlock full details and navigate the collection.', 'books-manager' ); ?>
            </p>
        </div>
        <?php echo books_manager_render_book_list( array( 'paged' => get_query_var( 'paged', 1 ) ) ); ?>
    <?php endif; ?>
</div>
<?php get_footer();
