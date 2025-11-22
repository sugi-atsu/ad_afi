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
        $json_data = wp_unslash($_POST['ranking_lp_json_data']);

        // JSONデータを一度デコードして、再度エンコードすることで、
        // 不正な文字やエスケープの問題を解消し、安全なJSON文字列を保証する
        $decoded_data = json_decode($json_data, true);

        // データが空、もしくはJSONとして不正な場合はメタデータを削除
        if (empty($json_data)) {
            delete_post_meta($post_id, '_cl_ranking_lp_data');
        } else {
            // 正常なJSONデータであれば、再エンコードして保存
            update_post_meta($post_id, '_cl_ranking_lp_data', json_encode($decoded_data, JSON_UNESCAPED_UNICODE));
        }
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
        wp_enqueue_style('admin-lp-style', get_stylesheet_directory_uri() . '/assets/css/admin-lp-style.css', array(), filemtime(get_stylesheet_directory() . '/assets/css/admin-lp-style.css'));
    }
}
add_action('admin_enqueue_scripts', 'load_lp_admin_scripts');


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
        $json_data = get_post_meta($post->ID, '_cl_ranking_lp_data', true);
        $items_data = json_decode($json_data, true);
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