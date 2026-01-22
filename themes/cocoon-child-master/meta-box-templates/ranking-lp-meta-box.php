<?php
/**
 * ランキングLP用メタボックスの表示テンプレート（改修版）
 */
if (!defined('ABSPATH')) exit;
?>

<!-- 商品アイテムが追加されるラッパー -->
<p>
    <label for="ranking-lp-section-title" style="font-weight:bold;">ランキングセクションタイトル:</label><br>
    <input type="text" name="ranking_lp_section_title" id="ranking-lp-section-title" value="<?php echo isset($section_title) ? esc_attr($section_title) : ''; ?>" style="width: 100%; max-width: 600px; margin-bottom: 10px;">
    <br>
    <label for="ranking-lp-comparison-title" style="font-weight:bold;">比較表セクションタイトル:</label><br>
    <input type="text" name="ranking_lp_comparison_title" id="ranking-lp-comparison-title" value="<?php echo isset($comparison_title) ? esc_attr($comparison_title) : ''; ?>" style="width: 100%; max-width: 600px;">
</p>
<hr>
<div id="ranking-items-wrapper"></div>

<!-- 「商品を追加」ボタン -->
<button type="button" id="add-ranking-item-btn" class="button button-primary">商材を追加</button>

<!-- データ保存用の隠しtextarea -->
<!-- データ保存用の隠しtextarea -->
<?php
// textareaには常にBase64を入れる（JSの互換性のため、あるいはJS側でBase64を期待するようにする）
// $json_data は functions.php の show_ranking_lp_meta_box_html で取得されているはずだが、
// ここで再取得するか、functions.php から渡される変数を考慮する。
// show_ranking_lp_meta_box_html では $json_data = get_post_meta(...) している。
// それがBase64かJSONか判定して、textareaにはBase64を入れるのが安全。

$saved_data_for_textarea = $json_data; // functions.phpから渡された値
$first_char = substr(trim($saved_data_for_textarea), 0, 1);
if ($first_char === '{' || $first_char === '[') {
    // JSONならBase64エンコード
    $value_for_textarea = base64_encode($saved_data_for_textarea);
} else {
    // すでにBase64ならそのまま
    $value_for_textarea = $saved_data_for_textarea;
}
?>
<textarea name="ranking_lp_json_data" id="ranking-lp-json-data" style="display:none;"><?php echo esc_textarea($value_for_textarea); ?></textarea>

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
            <h4>比較表用項目 (判定 + テキスト)</h4>
            <div class="sub-items-wrapper" data-sub-key="comparisonItems">
                <!-- JSによって比較項目がここに追加されます -->
            </div>
            <button type="button" class="button add-sub-item-btn" data-type="comparison">比較項目を追加</button>

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

<!-- JSで「比較項目」を複製するためのテンプレート -->
<div id="comparison-item-template" style="display:none;">
    <div class="sub-item">
        <span class="sub-item-handle">↕︎</span>
        <label>項目名: <input type="text" class="sub-item-field" data-key="label" placeholder="例: 買取スピード"></label>
        <label>判定:
            <select class="sub-item-field" data-key="status">
                <option value="none">-</option>
                <option value="double-circle">◎ (とても良い)</option>
                <option value="circle">◯ (良い)</option>
                <option value="triangle">△ (普通)</option>
                <option value="cross">× (悪い)</option>
            </select>
        </label>
        <label>補足: <input type="text" class="sub-item-field" data-key="text" placeholder="例: 即日現金"></label>
        <button type="button" class="button-link-delete remove-sub-item-btn">&times;</button>
    </div>
</div>