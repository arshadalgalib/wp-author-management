<?php
/**
 * Author Management Meta Box for WordPress
 * Adds a meta box to manage post authors with username (Latin-only) and nickname (multilingual) support.
 * Version: 1.0
 * Author: [Your Name]
 */

// Add meta box to post edit screen
function custom_author_management_meta_box() {
    add_meta_box('author_management_box', 'Author Management', 'render_author_management_box', 'post', 'side', 'high');
}
add_action('add_meta_boxes', 'custom_author_management_meta_box');

// Render meta box content with attractive styling
function render_author_management_box($post) {
    wp_nonce_field('author_management_nonce', 'nonce');
    $current_author = get_post_field('post_author', $post->ID);
    $users = get_users(['role__in' => ['subscriber', 'author', 'editor', 'administrator'], 'orderby' => 'display_name']);
    ?>
    <div class="author-management-container">
        <label class="section-label">Select Existing Author</label>
        <select name="existing_author" class="author-select">
            <option value="">Choose an author</option>
            <?php foreach ($users as $user) : ?>
                <option value="<?php echo esc_attr($user->ID); ?>" <?php selected($current_author, $user->ID); ?>>
                    <?php echo esc_html($user->display_name . ' (' . $user->user_email . ')'); ?>
                </option>
            <?php endforeach; ?>
        </select>

        <div class="new-author-section">
            <h4>Create New Author</h4>
            <label>Username (Latin only)<br>
                <input type="text" id="new_author_username" name="new_author_username" class="author-input" placeholder="e.g., rahimkhan">
            </label>
            <label>Nickname (Any language)<br>
                <input type="text" id="new_author_nickname" name="new_author_nickname" class="author-input" placeholder="e.g., রহিম খান">
            </label>
            <label>Email<br>
                <input type="email" id="new_author_email" name="new_author_email" class="author-input" placeholder="e.g., rahim@example.com">
            </label>
            <label>Role<br>
                <select id="new_author_role" name="new_author_role" class="author-select">
                    <option value="subscriber">Subscriber</option>
                    <option value="author">Author</option>
                    <option value="editor">Editor</option>
                    <option value="administrator">Administrator</option>
                </select>
            </label>
            <button type="button" id="create_author_button" class="author-button">Create Author</button>
            <div id="author_creation_message" class="message-box"></div>
        </div>
    </div>

    <style>
        .author-management-container { padding: 10px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; }
        .section-label { display: block; font-weight: 600; margin-bottom: 8px; color: #333; }
        .author-select, .author-input { 
            width: 100%; padding: 8px; margin-bottom: 12px; border: 1px solid #ddd; border-radius: 4px; 
            box-sizing: border-box; font-size: 14px; transition: border-color 0.3s; 
        }
        .author-select:focus, .author-input:focus { border-color: #007cba; outline: none; }
        .new-author-section h4 { margin: 15px 0 10px; color: #007cba; font-size: 16px; }
        .author-button { 
            background: #007cba; color: white; border: none; padding: 10px 15px; border-radius: 4px; 
            cursor: pointer; font-weight: 500; transition: background 0.3s; width: 100%; 
        }
        .author-button:hover { background: #005d9a; }
        .message-box { margin-top: 10px; font-size: 13px; padding: 5px; }
        .message-box span { padding: 5px; display: block; border-radius: 3px; }
        .message-box span[style*="color: green"] { background: #e6ffe6; }
        .message-box span[style*="color: red"] { background: #ffe6e6; }
    </style>

    <script>
    jQuery(function($) {
        $('#create_author_button').on('click', function() {
            var data = {
                action: 'create_new_author',
                username: $('#new_author_username').val(),
                nickname: $('#new_author_nickname').val(),
                email: $('#new_author_email').val(),
                role: $('#new_author_role').val(),
                nonce: $('#nonce').val()
            };

            $.post('<?php echo admin_url('admin-ajax.php'); ?>', data, function(response) {
                if (response.success) {
                    $('#author_creation_message').html('<span style="color: green;">Author created</span>');
                    $('select[name="existing_author"]').append(
                        `<option value="${response.data.user_id}" selected>${data.nickname} (${data.email})</option>`
                    );
                    $('#new_author_username, #new_author_nickname, #new_author_email').val('');
                } else {
                    $('#author_creation_message').html(`<span style="color: red">${response.data.message}</span>`);
                }
            }).fail(function() {
                $('#author_creation_message').html('<span style="color: red">Request failed</span>');
            });
        });
    });
    </script>
    <?php
}

// AJAX handler for creating new author
function create_new_author_callback() {
    check_ajax_referer('author_management_nonce', 'nonce');
    if (!current_user_can('edit_users')) {
        wp_send_json_error(['message' => 'Permission denied']);
    }

    $username = sanitize_user($_POST['username'], true);
    $nickname = sanitize_text_field($_POST['nickname']);
    $email = sanitize_email($_POST['email']);
    $role = in_array($_POST['role'], ['subscriber', 'author', 'editor', 'administrator']) ? $_POST['role'] : 'subscriber';

    if (empty($username) || empty($nickname) || !is_email($email)) {
        wp_send_json_error(['message' => 'Invalid input']);
    }
    if (email_exists($email)) {
        wp_send_json_error(['message' => 'Email already exists']);
    }

    $base_username = $username;
    $counter = 1;
    while (username_exists($username)) {
        $username = $base_username . $counter++;
    }

    $user_id = wp_create_user($username, wp_generate_password(12, true), $email);
    if (is_wp_error($user_id)) {
        wp_send_json_error(['message' => $user_id->get_error_message()]);
    }

    wp_update_user([
        'ID' => $user_id,
        'nickname' => $nickname,
        'display_name' => $nickname,
        'role' => $role
    ]);

    wp_send_json_success(['user_id' => $user_id]);
}
add_action('wp_ajax_create_new_author', 'create_new_author_callback');

// Save author on post update
function save_author_management_data($post_id) {
    if (!wp_verify_nonce($_POST['nonce'] ?? '', 'author_management_nonce') || 
        (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) || 
        !current_user_can('edit_post', $post_id)) {
        return;
    }

    if (!empty($_POST['existing_author']) && $author_id = intval($_POST['existing_author'])) {
        wp_update_post(['ID' => $post_id, 'post_author' => $author_id]);
    }
}
add_action('save_post', 'save_author_management_data');

// Display author on frontend
function display_post_author_name($content) {
    if (is_single() && in_the_loop() && is_main_query()) {
        $author_name = get_the_author_meta('display_name', get_post_field('post_author'));
        return '<div class="post-author" style="margin: 15px 0; font-style: italic; color: #555;">Written by: ' . esc_html($author_name) . '</div>' . $content;
    }
    return $content;
}
add_filter('the_content', 'display_post_author_name');

// Enqueue jQuery
add_action('admin_enqueue_scripts', function() {
    wp_enqueue_script('jquery');
});
