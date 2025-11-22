// WordPressのグローバル変数から必要なコンポーネントを取得
const { registerBlockType } = wp.blocks;
const { InspectorControls, useBlockProps, RichText, PanelColorSettings } = wp.blockEditor;
const { PanelBody, TextControl, ToggleControl } = wp.components;
const { __ } = wp.i18n;
const { createElement, Fragment } = wp.element;

registerBlockType('custom-blocks/cta-button', {
    edit: ({ attributes, setAttributes }) => {
        const { text, url, opensInNewTab, backgroundColor, textColor } = attributes;

        // ★★★ 外側のdivに useBlockProps を適用 ★★★
        const blockProps = useBlockProps(); 

        return createElement(
            Fragment,
            {},
            createElement(
                InspectorControls,
                {},
                createElement(
                    PanelBody,
                    { title: __('リンク設定', 'cocoon-child-master') },
                    createElement(TextControl, {
                        label: __('URL', 'cocoon-child-master'),
                        value: url,
                        onChange: (newUrl) => setAttributes({ url: newUrl }),
                    }),
                    createElement(ToggleControl, {
                        label: __('新しいタブで開く', 'cocoon-child-master'),
                        checked: opensInNewTab,
                        onChange: () => setAttributes({ opensInNewTab: !opensInNewTab }),
                    })
                ),
                createElement(PanelColorSettings, {
                    title: __('色設定', 'cocoon-child-master'),
                    colorSettings: [
                        {
                            value: backgroundColor,
                            onChange: (newColor) => setAttributes({ backgroundColor: newColor }),
                            label: __('背景色', 'cocoon-child-master'),
                        },
                        {
                            value: textColor,
                            onChange: (newColor) => setAttributes({ textColor: newColor }),
                            label: __('テキスト色', 'cocoon-child-master'),
                        },
                    ],
                })
            ),
            // ★★★ ここからがエディタ上の見た目 ★★★
            // useBlockProps を適用したdivで全体を囲む
            createElement(
                'div',
                blockProps, // ← divに適用
                createElement(RichText, {
                    // RichTextにはuseBlockPropsを適用しない
                    tagName: 'a',
                    className: 'custom-cta-button', // 独自のクラスを適用
                    style: { // スタイルは直接適用
                        backgroundColor: backgroundColor,
                        color: textColor,
                    },
                    value: text,
                    onChange: (newText) => setAttributes({ text: newText }),
                    placeholder: __('ボタンのテキスト...', 'cocoon-child-master'),
                    allowedFormats: [],
                })
            )
        );
    },

    save: () => {
        return null;
    },
});