(function ($) {
    'use strict';

    function replaceBooksList(html) {
        var $wrapper = $('#books-manager-list-wrapper');
        if ($wrapper.length) {
            $wrapper.replaceWith(html);
        }
    }

    $(document).on('submit', '#books-manager-filter-form', function (event) {
        event.preventDefault();

        var $form = $(this);
        var requestData = {
            action: 'books_manager_filter_books',
            nonce: booksManager.nonce,
            books_genre: $form.find('[name="books_genre"]').val(),
            books_author: $form.find('[name="books_author"]').val(),
            books_page: $form.find('[name="books_page"]').val() || 1,
        };

        $.post(booksManager.ajax_url, requestData, function (response) {
            if (response.success && response.data && response.data.html) {
                replaceBooksList(response.data.html);
            } else {
                $('#books-manager-list-wrapper').html('<div class="books-manager-error">' + (response.data.message || 'Unable to load books.') + '</div>');
            }
        });
    });

    $(document).on('click', '.books-manager-pagination a', function (event) {
        var url = $(this).attr('href');

        if (!url) {
            return;
        }

        event.preventDefault();
        var params = new URLSearchParams(url.split('?')[1] || '');
        var page = params.get('books_page') || 1;
        var $form = $('#books-manager-filter-form');

        if ($form.length) {
            $form.find('[name="books_page"]').val(page);
            $form.submit();
        }
    });
})(jQuery);
