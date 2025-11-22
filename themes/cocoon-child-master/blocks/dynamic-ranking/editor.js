wp.blocks.registerBlockType('custom-blocks/dynamic-ranking', {
    edit: () => {
        return wp.element.createElement(
            'div', 
            { style: { padding: '20px', backgroundColor: '#e9f5ff', border: '2px dashed #7cb9e8' } },
            'ここに「動的ランキング」が表示されます。（順序はメタボックスで管理）'
        );
    },
    save: () => null,
});