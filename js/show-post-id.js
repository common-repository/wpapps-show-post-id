// JavaScript function to fetch, join, copy the selected post IDs, and show inline notification
jQuery(document).ready(function ($) {
    // Check if the user applies the 'Copy Post IDs' bulk action
    $('#doaction, #doaction2').click(function (e) {
        if ($('select[name="action"]').val() === 'copy_post_ids' || $('select[name="action2"]').val() === 'copy_post_ids') {
            e.preventDefault();  // Prevent the default action
            var selectedPostIds = [];

            // Fetch all selected post IDs
            $('tbody th.check-column input[type="checkbox"]:checked').each(function () {
                var postId = $(this).closest('tr').attr('id').replace('post-', '');
                selectedPostIds.push(postId);
            });

            // Join the post IDs with commas
            var joinedPostIds = selectedPostIds.join(', ');

            // Copy the result to the clipboard
            var $temp = $("<input>");
            $("body").append($temp);
            $temp.val(joinedPostIds).select();
            document.execCommand("copy");
            $temp.remove();

            // Display inline notification below the bulk action dropdown
            var notificationMessage = '<div class="notice notice-success inline-notification"><p>Copied Post IDs: ' + joinedPostIds + '</p></div>';
            $('.tablenav.top .bulkactions').append(notificationMessage);

            // Fade out the notification after 5 seconds
            setTimeout(function () {
                $('.inline-notification').fadeOut('slow', function () {
                    $(this).remove();
                });
            }, 5000);
        }
    });
});
