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

    <footer class="lp-custom-footer" style="margin-top: 40px; padding: 20px 20px 5px 20px; text-align: center; border-top: 1px solid #ddd;">
        <nav class="lp-footer-nav">
            <a href="<?php echo home_url('/privacy'); ?>">プライバシーポリシー</a>
            <span style="margin: 0 10px; color: #ccc;">|</span>
            <a href="<?php echo home_url('/disclaimer'); ?>">免責事項</a>
            <span style="margin: 0 10px; color: #ccc;">|</span>
            <a href="<?php echo home_url('/contact'); ?>">お問い合わせ</a>
        </nav>
        <div class="copyright" style="margin-top: 15px; font-size: 12px; color: #666;">
            &copy; <?php echo date('Y'); ?> <?php bloginfo('name'); ?> All Rights Reserved.
        </div>
    </footer>
</div>

<?php get_footer(); ?>