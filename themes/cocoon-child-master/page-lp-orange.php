<?php
/*
Template Name: LPテンプレート (オレンジ)
Template Post Type: page
*/
get_header(); ?>

<div id="primary" class="content-area">
    <main id="main" class="site-main">

        <?php
        // WordPressのループを開始し、ブロックエディタのコンテンツを出力します
        if (have_posts()) {
            while (have_posts()) {
                the_post();
                the_content();
            }
        }
        ?>
        
    </main>
</div>

<?php get_footer(); ?>