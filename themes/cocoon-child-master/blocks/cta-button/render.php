<?php
/**
 * "カスタムCTAボタン" ブロックのレンダリング用PHP
 *
 * @see /blocks/cta-button/block.json
 * $attributes 変数には、エディタで設定された値が格納されています。
 */

// 属性のデフォルト値を設定
$attributes = wp_parse_args(
    $attributes,
    [
        'text' => 'ボタンのテキストを入力',
        'url' => '#',
        'opensInNewTab' => false,
        'backgroundColor' => '#f5a623',
        'textColor' => '#ffffff',
        'align' => '',
    ]
);

$text = $attributes['text'];
$url = $attributes['url'];
$opens_in_new_tab = $attributes['opensInNewTab'];
$background_color = $attributes['backgroundColor'];
$text_color = $attributes['textColor'];

// アライメントクラスの処理
$wrapper_attributes = get_block_wrapper_attributes();
if ( ! empty( $attributes['align'] ) ) {
    $wrapper_attributes = str_replace(
        'class="',
        'class="align' . esc_attr( $attributes['align'] ) . ' ',
        $wrapper_attributes
    );
}

// スタイルをインラインで生成
$styles = sprintf(
    'background-color: %s; color: %s;',
    esc_attr( $background_color ),
    esc_attr( $text_color )
);

// rel属性の生成
$rel = 'noopener noreferrer';
if ( strpos($url, home_url()) === false ) {
    // 外部リンクの場合にsponsoredを追加する例
    $rel .= ' sponsored';
}
?>

<div <?php echo $wrapper_attributes; ?>>
    <a 
        href="<?php echo esc_url( $url ); ?>" 
        class="custom-cta-button lp-track-cta" 
        style="<?php echo $styles; ?>"
        <?php if ( $opens_in_new_tab ) : ?>
            target="_blank" rel="<?php echo esc_attr( $rel ); ?>"
        <?php endif; ?>
    >
        <?php echo wp_kses_post( $text ); ?>
    </a>
</div>

<style>
/* このブロック専用の簡易的なスタイル */
.custom-cta-button {
    display: inline-block;
    padding: 15px 35px;
    font-size: 18px;
    font-weight: bold;
    text-align: center;
    text-decoration: none;
    border-radius: 5px;
    transition: opacity 0.2s;
}
.custom-cta-button:hover {
    opacity: 0.85;
}
.alignwide .custom-cta-button,
.alignfull .custom-cta-button {
    display: block;
    width: 100%;
}
</style>