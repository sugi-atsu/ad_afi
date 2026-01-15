<?php
/**
 * Title: [LP] 比較カード
 * Slug: lp-patterns/comparison-cards
 * Categories: lp-parts
 * Description: 複数の商材を横スクロール可能な比較表形式で表示します。
 * Keywords: LP, 比較, カード, 比較表
 */
?>
<!-- wp:group {"className":"lp-section comparison-section"} -->
<div class="wp-block-group lp-section comparison-section">
    <!-- wp:heading {"textAlign":"center","level":2,"className":"lp-section-title"} -->
    <h2 class="wp-block-heading has-text-align-center lp-section-title">サービス比較表</h2>
    <!-- /wp:heading -->

    <!-- wp:html -->
    <div id="dynamic-comparison-cards-app" class="comparison-table-wrapper">
        <p style="text-align:center; padding: 20px;">比較データを読み込んでいます...</p>
    </div>
    <!-- /wp:html -->
</div>
<!-- /wp:group -->
