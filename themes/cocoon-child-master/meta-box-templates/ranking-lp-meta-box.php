<?php
/**
 * ランキングLP用メタボックスの表示テンプレート（改修版）
 */
if (!defined('ABSPATH')) exit;
?>

<!-- 商品アイテムが追加されるラッパー -->
<div id="ranking-items-wrapper"></div>

<!-- 「商品を追加」ボタン -->
<button type="button" id="add-ranking-item-btn" class="button button-primary">商材を追加</button>

<!-- データ保存用の隠しtextarea -->
<textarea name="ranking_lp_json_data" id="ranking-lp-json-data" style="display:none;"><?php echo esc_attr($json_data); ?></textarea>

<!-- JSで「商材」全体を複製するためのテンプレート -->
<div id="ranking-item-template" style="display:none;">
    <div class="ranking-item">
        <div class="item-header">
            <span class="item-handle">↕︎</span>
            <strong class="item-title">商材</strong>
            <button type="button" class="button button-small remove-item-btn">削除</button>
        </div>
        <div class="item-body">
            <h4>基本情報</h4>
            <p><label>商材名: <input type="text" class="item-field" data-key="productName"></label></p>
            <p><label>キャッチコピー: <input type="text" class="item-field" data-key="catchphrase"></label></p>
            <p><label>アフィリエイトリンク: <input type="url" class="item-field" data-key="affiliateLink"></label></p>
            <p><label>画像URL: <input type="url" class="item-field" data-key="imageUrl"></label></p>
            <p><label>総合評価 (1-5): <input type="number" class="item-field" data-key="overallRating" min="0" max="5" step="0.1"></label></p>

            <hr>
            <h4>評価項目 (星評価)</h4>
            <div class="sub-items-wrapper" data-sub-key="ratingItems">
                <!-- JSによって評価項目がここに追加されます -->
            </div>
            <button type="button" class="button add-sub-item-btn" data-type="rating">評価項目を追加</button>

            <hr>
            <h4>スペック項目 (テキスト)</h4>
            <div class="sub-items-wrapper" data-sub-key="specItems">
                <!-- JSによってスペック項目がここに追加されます -->
            </div>
            <button type="button" class="button add-sub-item-btn" data-type="spec">スペック項目を追加</button>

            <hr>
            <h4>サマリー用箇条書き</h4>
            <div class="sub-items-wrapper" data-sub-key="summaryPoints">
                <!-- JSによってサマリー用箇条書きがここに追加されます -->
            </div>
            <button type="button" class="button add-sub-item-btn" data-type="summary">箇条書きを追加</button>

            <hr>
            <h4>商材説明詳細</h4>
            <p><label>タイトル: <input type="text" class="item-field" data-key="productDetailTitle"></label></p>
            <p><label>本文: <textarea class="item-field" data-key="productDetail" rows="4"></textarea></label></p>

            <hr>
            <h4>CTA (Call To Action)</h4>
            <p><label>CTAマイクロコピー: <input type="text" class="item-field" data-key="ctaMicrocopy"></label></p>
            <p><label>CTAボタン テキスト: <input type="text" class="item-field" data-key="ctaButtonText"></label></p>
        </div>
    </div>
</div>

<!-- JSで「評価項目」を複製するためのテンプレート -->
<div id="rating-item-template" style="display:none;">
    <div class="sub-item">
        <span class="sub-item-handle">↕︎</span>
        <label>項目名: <input type="text" class="sub-item-field" data-key="label"></label>
        <label>評価値(1-5): <input type="number" class="sub-item-field" data-key="value" min="0" max="5" step="0.1"></label>
        <button type="button" class="button-link-delete remove-sub-item-btn">&times;</button>
    </div>
</div>

<!-- JSで「スペック項目」を複製するためのテンプレート -->
<div id="spec-item-template" style="display:none;">
    <div class="sub-item">
        <span class="sub-item-handle">↕︎</span>
        <label>項目名: <input type="text" class="sub-item-field" data-key="label"></label>
        <label>内容: <textarea class="sub-item-field" data-key="value" rows="2"></textarea></label>
        <button type="button" class="button-link-delete remove-sub-item-btn">&times;</button>
    </div>
</div>

<!-- JSで「サマリー用箇条書き」を複製するためのテンプレート -->
<div id="summary-point-template" style="display:none;">
    <div class="sub-item">
        <span class="sub-item-handle">↕︎</span>
        <label>箇条書きテキスト: <input type="text" class="sub-item-field" data-key="point"></label>
        <button type="button" class="button-link-delete remove-sub-item-btn">&times;</button>
    </div>
</div>