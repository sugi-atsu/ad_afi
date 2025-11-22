<?php
/*
 * Template Name: LPデザイン：クールブルー
 * Template Post Type: page
 */

add_filter('get_page_type', function ($type) {
    return 'page_content'; // 'page_content' が「本文のみ」タイプを示す内部名
}, 9999);

get_header(); // Cocoonのヘッダーを読み込む
?>

<div id="primary" class="content-area">
    <main id="main" class="site-main" role="main">
        <?php
        while (have_posts()):
            the_post();

            // ブロックエディタのコンテンツがここに出力される
            the_content();

        endwhile;
        ?>
    </main>
</div>

<?php
get_footer(); // Cocoonのフッターを読み込む