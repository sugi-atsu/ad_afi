<?php
// Cocoon Childを有効化するために必要です。
if (!defined('ABSPATH'))
    exit;

/**
 * Cocoon既定のJS・CSSの読み込みを必要に応じてOFFにする
 */
//add_filter('is_cocoon_load_css_libraries', '__return_false');
//add_filter('is_cocoon_load_js_libraries', '__return_false');
//add_filter('is_cocoon_load_web_font_lazy', '__return_false');


///////////////////////////////////////////
// 以下に子テーマ用の関数を記述
///////////////////////////////////////////

/****************************************
 * LPシステムの初期化 (ブロックとパターンの登録)
 ****************************************/
function initialize_lp_system()
{
    // カスタムブロックの登録
    register_block_type(__DIR__ . '/blocks/cta-button');
    register_block_type(__DIR__ . '/blocks/dynamic-ranking');

    // ブロックパターンカテゴリの登録
    register_block_pattern_category('lp-parts', array('label' => 'LP専用パーツ'));

}
add_action('init', 'initialize_lp_system');


/****************************************
 * 管理画面の拡張 (メタボックスとスクリプト)
 ****************************************/
function setup_lp_admin_area()
{
    global $post;
    if (!$post)
        return;

    $template_file = get_post_meta($post->ID, '_wp_page_template', true);
    $lp_templates = ['page-lp-orange.php', 'page-lp-coolblue.php'];

    if (in_array($template_file, $lp_templates)) {
        add_meta_box('ranking_lp_meta_box', 'ランキング商品データ', 'show_ranking_lp_meta_box_html', 'page', 'normal', 'high');
    }
}
add_action('add_meta_boxes', 'setup_lp_admin_area');

function show_ranking_lp_meta_box_html($post)
{
    wp_nonce_field('save_ranking_data_action', 'ranking_data_nonce');
    $json_data = get_post_meta($post->ID, '_cl_ranking_lp_data', true);
    $template_path = get_stylesheet_directory() . '/meta-box-templates/ranking-lp-meta-box.php';
    if (file_exists($template_path)) {
        require($template_path);
    }
}

// ★★★ ここからが欠落していたデータ保存の処理 ★★★
// デバッグログ関数
function write_lp_debug_log($message)
{
    $log_file = get_stylesheet_directory() . '/lp_debug.log';
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($log_file, "[$timestamp] $message\n", FILE_APPEND);
}

// ★★★ ここからが欠落していたデータ保存の処理 ★★★
function save_ranking_lp_meta_box_data($post_id, $post)
{
    if (!isset($_POST['ranking_data_nonce']) || !wp_verify_nonce($_POST['ranking_data_nonce'], 'save_ranking_data_action'))
        return;
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
        return;
    if (!current_user_can('edit_page', $post_id))
        return;
    if (wp_is_post_revision($post_id) || wp_is_post_autosave($post_id))
        return;
    if ($post->post_type != 'page')
        return;

    // LPテンプレートを使用しているかチェック
    $template_file = get_post_meta($post_id, '_wp_page_template', true);
    $lp_templates = ['page-lp-orange.php', 'page-lp-coolblue.php'];
    if (!in_array($template_file, $lp_templates)) {
        return;
    }

    // 'ranking_lp_json_data' がPOSTされている場合のみ処理を実行
    if (isset($_POST['ranking_lp_json_data'])) {
        $raw_data = $_POST['ranking_lp_json_data'];
        write_lp_debug_log("SAVE START: Post ID $post_id");
        write_lp_debug_log("Raw POST data length: " . strlen($raw_data));
        
        // Base64エンコードされているかチェック
        $first_char = substr(trim($raw_data), 0, 1);
        if ($first_char !== '{' && $first_char !== '[') {
            // Base64のまま保存する (DB保存時の破損を防ぐため)
            update_post_meta($post_id, '_cl_ranking_lp_data', $raw_data);
            write_lp_debug_log("Saved as Base64. Length: " . strlen($raw_data));
        } else {
            // 従来のJSON生データの場合は、Base64に変換して保存する
            $base64_data = base64_encode(wp_unslash($raw_data));
            update_post_meta($post_id, '_cl_ranking_lp_data', $base64_data);
            write_lp_debug_log("Converted raw JSON to Base64 and saved. Length: " . strlen($base64_data));
        }
    } else {
        write_lp_debug_log("No ranking_lp_json_data in POST.");
    }
}
add_action('save_post_page', 'save_ranking_lp_meta_box_data', 10, 2);
// ★★★ ここまで ★★★

function load_lp_admin_scripts($hook)
{
    if ('post.php' != $hook && 'post-new.php' != $hook)
        return;
    global $post;
    if (!$post || 'page' != $post->post_type)
        return;
    $template_file = get_post_meta($post->ID, '_wp_page_template', true);
    $lp_templates = ['page-lp-orange.php', 'page-lp-coolblue.php'];
    if (in_array($template_file, $lp_templates)) {
        wp_enqueue_script('admin-lp-script', get_stylesheet_directory_uri() . '/assets/js/admin-lp-script.js', array('jquery', 'jquery-ui-sortable'), filemtime(get_stylesheet_directory() . '/assets/js/admin-lp-script.js'), true);
        
        // データを安全にJSに渡す
        $saved_data = get_post_meta($post->ID, '_cl_ranking_lp_data', true);
        write_lp_debug_log("LOAD: Post ID " . $post->ID);
        write_lp_debug_log("Loaded meta data length: " . strlen($saved_data));
        
        // Base64かJSONかを判定してデコード
        $first_char = substr(trim($saved_data), 0, 1);
        if ($first_char === '{' || $first_char === '[') {
            // 旧データ(JSON)
            $json_data = $saved_data;
            write_lp_debug_log("Detected Legacy JSON format.");
        } else {
            // 新データ(Base64)
            $decoded = base64_decode($saved_data);
            if ($decoded) {
                $json_data = $decoded;
                write_lp_debug_log("Decoded Base64 to JSON.");
            } else {
                $json_data = $saved_data; // デコード失敗？
                write_lp_debug_log("Base64 decode failed or empty.");
            }
        }
        
        $items_data = json_decode($json_data, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            write_lp_debug_log("JSON Decode Error: " . json_last_error_msg());
        }
        
        wp_localize_script('admin-lp-script', 'rankingLpAdminData', array('items' => is_array($items_data) ? $items_data : []));

        wp_enqueue_style('admin-lp-style', get_stylesheet_directory_uri() . '/assets/css/admin-lp-style.css', array(), filemtime(get_stylesheet_directory() . '/assets/css/admin-lp-style.css'));
    }
}
add_action('admin_enqueue_scripts', 'load_lp_admin_scripts');

// デバッグログを管理画面に表示
function show_lp_debug_log_in_admin()
{
    $screen = get_current_screen();
    if ($screen && $screen->id === 'page') {
        $log_file = get_stylesheet_directory() . '/lp_debug.log';
        if (file_exists($log_file)) {
            $log_content = file_get_contents($log_file);
            // 最後の20行だけ表示するなど調整可能だが、まずは全部出す
            echo '<div class="notice notice-info is-dismissible">';
            echo '<h3>LP Debug Log</h3>';
            echo '<pre style="max-height: 300px; overflow: auto; background: #f0f0f0; padding: 10px;">';
            echo esc_html($log_content);
            echo '</pre>';
            echo '<p><button type="button" class="button" onclick="document.cookie=\'clear_lp_log=1\';location.reload();">ログをクリア</button></p>';
            echo '</div>';
        }
    }

    // ログクリア処理
    if (isset($_COOKIE['clear_lp_log']) && $_COOKIE['clear_lp_log'] == '1') {
        $log_file = get_stylesheet_directory() . '/lp_debug.log';
        if (file_exists($log_file)) {
            file_put_contents($log_file, '');
        }
        setcookie('clear_lp_log', '', time() - 3600);
    }
}
add_action('admin_notices', 'show_lp_debug_log_in_admin');


/****************************************
 * フロントエンドのスクリプトとスタイルの読み込み
 ****************************************/
function enqueue_lp_front_scripts()
{
    global $post;
    if (!$post)
        return;

    $is_lp_template = is_page_template('page-lp-orange.php') || is_page_template('page-lp-coolblue.php');

    if ($is_lp_template) {
        wp_enqueue_script(
            'lp-front-script',
            get_stylesheet_directory_uri() . '/assets/js/front-lp-script.js',
            array(),
            filemtime(get_stylesheet_directory() . '/assets/js/front-lp-script.js'),
            true
        );
        $saved_data = get_post_meta($post->ID, '_cl_ranking_lp_data', true);
        
        write_lp_debug_log("FRONTEND LOAD: Post ID " . $post->ID);
        write_lp_debug_log("Frontend loaded meta length: " . strlen($saved_data));

        // Base64かJSONかを判定してデコード
        $first_char = substr(trim($saved_data), 0, 1);
        if ($first_char === '{' || $first_char === '[') {
            // 旧データ(JSON)
            $json_data = $saved_data;
            write_lp_debug_log("Frontend detected Legacy JSON.");
        } else {
            // 新データ(Base64)
            $decoded = base64_decode($saved_data);
            if ($decoded) {
                $json_data = $decoded;
                write_lp_debug_log("Frontend decoded Base64 successfully.");
            } else {
                $json_data = $saved_data;
                write_lp_debug_log("Frontend Base64 decode FAILED.");
            }
        }

        $items_data = json_decode($json_data, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            write_lp_debug_log("Frontend JSON Decode Error: " . json_last_error_msg());
        } else {
            write_lp_debug_log("Frontend JSON Decode Success. Items count: " . (is_array($items_data) ? count($items_data) : 0));
        }

        wp_localize_script('lp-front-script', 'rankingLpData', array('items' => is_array($items_data) ? $items_data : []));
    }

    // 共通のブロックスタイル
    $common_style_path = get_stylesheet_directory() . '/assets/css/patterns-common.css';
    wp_enqueue_style('lp-patterns-common-style', get_stylesheet_directory_uri() . '/assets/css/patterns-common.css', array(), filemtime($common_style_path));

    // 各パターン・ブロックのCSSを読み込み
    $styles_to_load = [
        'lp-pattern-ranking-summary' => '/assets/css/pattern-ranking-summary.css',
        'lp-pattern-ranking-detailed' => '/assets/css/pattern-ranking-detailed.css',
        'lp-pattern-comparison-table' => '/assets/css/pattern-comparison-table.css',
        'lp-block-cta-button' => '/assets/css/block-cta-button.css',
    ];

    foreach ($styles_to_load as $handle => $path) {
        $file_path = get_stylesheet_directory() . $path;
        if (file_exists($file_path)) {
            wp_enqueue_style($handle, get_stylesheet_directory_uri() . $path, array('lp-patterns-common-style'), filemtime($file_path));
        }
    }

    // テンプレートごとの色指定スタイル
    if (is_page_template('page-lp-orange.php')) {
        wp_enqueue_style('lp-orange-style', get_stylesheet_directory_uri() . '/assets/css/style-lp-orange.css', array('lp-patterns-common-style'), filemtime(get_stylesheet_directory() . '/assets/css/style-lp-orange.css'));
    } elseif (is_page_template('page-lp-coolblue.php')) {
        wp_enqueue_style('lp-coolblue-style', get_stylesheet_directory_uri() . '/assets/css/style-lp-coolblue.css', array('lp-patterns-common-style'), filemtime(get_stylesheet_directory() . '/assets/css/style-lp-coolblue.css'));
    }
}
add_action('wp_enqueue_scripts', 'enqueue_lp_front_scripts');

/**
 * LPテンプレートに共通のbodyクラスを追加
 */
function add_lp_template_body_class($classes)
{
    if (is_page_template('page-lp-orange.php') || is_page_template('page-lp-coolblue.php')) {
        $classes[] = 'lp-template';
    }
    return $classes;
}
add_filter('body_class', 'add_lp_template_body_class');

/****************************************
 * ブロックエディタのカスタマイズ
 ****************************************/
function customize_lp_block_editor($allowed_block_types, $editor_context)
{
    if (!empty($editor_context->post) && $editor_context->post->post_type === 'page') {
        $template_file = get_post_meta($editor_context->post->ID, '_wp_page_template', true);
        $lp_templates = ['page-lp-orange.php', 'page-lp-coolblue.php'];
        if (in_array($template_file, $lp_templates)) {
            $registered_blocks = WP_Block_Type_Registry::get_instance()->get_all_registered();
            $allowed_blocks = array_keys($registered_blocks);
            $hidden_blocks = [
                'cocoon-blocks/ranking',
                'cocoon-blocks/toc',
                'cocoon-blocks/blogcard',
            ];
            return array_diff($allowed_blocks, $hidden_blocks);
        }
    }
    return $allowed_block_types;
}
add_filter('allowed_block_types_all', 'customize_lp_block_editor', 10, 2);

/****************************************
 * LPテンプレートのレイアウトを強制的に上書きする
 ****************************************/
function ultimate_force_lp_layout_start()
{
    if (is_page_template('page-lp-orange.php') || is_page_template('page-lp-coolblue.php')) {
        ob_start('remove_header_and_sidebar_from_html');
    }
}
add_action('template_redirect', 'ultimate_force_lp_layout_start');

function remove_header_and_sidebar_from_html($buffer)
{
    $buffer = preg_replace('/<header id="header".*?<\/header>/is', '', $buffer);
    $buffer = preg_replace('/<div id="sidebar".*?<\/div>/is', '', $buffer);
    $buffer = preg_replace('/<aside[^>]+widget.*?<\/aside>/is', '', $buffer);
    $buffer = preg_replace('/<div id="comments".*?<\/div>/is', '', $buffer);
    $buffer = preg_replace('/<footer id="footer".*?<\/footer>/is', '', $buffer);
    $buffer = preg_replace('/(<div id="main".*?class="[^"]*")/is', '$1 main-full', $buffer);
    $buffer = str_replace('main-wrap', 'main-wrap main-full', $buffer);
    return $buffer;
}