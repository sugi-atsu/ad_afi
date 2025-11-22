<?php
/**
 * Title: [LP] ランキング詳細
 * Slug: lp-patterns/ranking-detailed
 * Categories: lp-parts
 * Description: カスタムフィールドのデータを元に、ランキング形式で詳細カードを自動生成します。
 * Keywords: LP, ランキング, 詳細, 動的
 */
?>
<!-- wp:group {"className":"lp-section"} -->
<div class="wp-block-group lp-section">
    <!-- wp:heading {"textAlign":"center","level":2,"className":"lp-section-title"} -->
    <h2 class="wp-block-heading has-text-align-center lp-section-title">編集部おすすめ転職サービス</h2>
    <!-- /wp:heading -->

    <!-- wp:html -->
    <div id="dynamic-ranking-app">
        <p style="text-align:center; padding: 20px;">ランキング詳細を読み込んでいます...</p>
    </div>
    <!-- /wp:html -->
</div>
<!-- /wp:group -->