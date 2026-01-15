(function ($) {
  $(document).ready(function () {
    const wrapper = $("#ranking-items-wrapper");
    if (wrapper.length === 0) return;

    const hiddenTextarea = $("#ranking-lp-json-data");
    const mainTemplateHtml = $("#ranking-item-template").html();

    // 【新規追加】サブ項目のテンプレートHTMLを取得
    const ratingTemplateHtml = $("#rating-item-template").html();
    const specTemplateHtml = $("#spec-item-template").html();
    const summaryTemplateHtml = $("#summary-point-template").html();
    const comparisonTemplateHtml = $("#comparison-item-template").html();

    // =====================================================================
    // データ更新関数 (JSON生成)
    // =====================================================================
    function updateJsonData() {
      const allItemsData = [];
      wrapper.find(".ranking-item").each(function () {
        const $currentItem = $(this);
        const itemData = {};

        // 通常のフィールドを収集
        $currentItem.find(".item-field").each(function () {
          const key = $(this).data("key");
          if (key) {
            itemData[key] = $(this).val();
          }
        });

        // 【新規追加】ネストされた繰り返しフィールドを収集
        $currentItem.find(".sub-items-wrapper").each(function () {
          const subKey = $(this).data("sub-key");
          itemData[subKey] = [];
          $(this)
            .find(".sub-item")
            .each(function () {
              const subItemData = {};
              $(this)
                .find(".sub-item-field")
                .each(function () {
                  const key = $(this).data("key");
                  subItemData[key] = $(this).val();
                });
              itemData[subKey].push(subItemData);
            });
        });

        allItemsData.push(itemData);
      });
      // Base64エンコードして保存 (UTF-8対応)
      const jsonStr = JSON.stringify(allItemsData, null, 2);
      console.log("Saving Data (JSON):", jsonStr); // デバッグ用出力

      const base64Str = btoa(unescape(encodeURIComponent(jsonStr)));
      console.log("Saving Data (Base64):", base64Str); // デバッグ用出力

      hiddenTextarea.val(base64Str);
    }

    // =====================================================================
    // 初期描画関数
    // =====================================================================
    function renderItems() {
      wrapper.empty();
      let initialData = [];
      // 優先的に wp_localize_script で渡されたデータを使用
      // ただし、nullの場合は無視してフォールバック（textarea）を使う
      if (typeof rankingLpAdminData !== 'undefined' && Array.isArray(rankingLpAdminData.items)) {
        initialData = rankingLpAdminData.items;
      } else {
        // フォールバック: textareaから読み込み
        // textareaの中身はBase64になっているはず
        try {
          const rawVal = hiddenTextarea.val();
          if (rawVal) {
            // Base64デコード (UTF-8対応)
            const jsonStr = decodeURIComponent(escape(atob(rawVal)));
            const data = JSON.parse(jsonStr);
            if (Array.isArray(data)) {
              initialData = data;
            }
          }
        } catch (e) {
          console.error('Invalid Data in textarea (Fallback):', e);
          // 万が一JSONのままだった場合の救済（基本はないはずだが）
          try {
            const data = JSON.parse(hiddenTextarea.val());
            if (Array.isArray(data)) initialData = data;
          } catch (e2) { }
        }
      }

      if (initialData.length > 0) {
        initialData.forEach((itemData) => {
          const $newItem = $(mainTemplateHtml);

          // 通常のフィールドを描画
          $newItem.find(".item-field").each(function () {
            const key = $(this).data("key");
            if (itemData[key] !== undefined) {
              $(this).val(itemData[key]);
            }
          });

          // 【新規追加】ネストされた繰り返しフィールドを描画
          $newItem.find(".sub-items-wrapper").each(function () {
            const subKey = $(this).data("sub-key");
            const $subWrapper = $(this);
            if (Array.isArray(itemData[subKey])) {
              itemData[subKey].forEach((subItemData) => {
                let template;
                if (subKey === "ratingItems") template = ratingTemplateHtml;
                if (subKey === "specItems") template = specTemplateHtml;
                if (subKey === "summaryPoints") template = summaryTemplateHtml;
                if (subKey === "comparisonItems") template = comparisonTemplateHtml;

                const $newSubItem = $(template);
                $newSubItem.find(".sub-item-field").each(function () {
                  const key = $(this).data("key");
                  $(this).val(subItemData[key]);
                });
                $subWrapper.append($newSubItem);
              });
            }
          });

          wrapper.append($newItem);
        });
      }
    }

    // =====================================================================
    // イベントハンドラ
    // =====================================================================

    // 「商材を追加」ボタン
    $("#add-ranking-item-btn").on("click", function () {
      const $newItem = $(mainTemplateHtml);
      wrapper.append($newItem);
      // 新しく追加した項目に対してサブ項目のソータブルを有効化
      enableSubItemSortable($newItem.find(".sub-items-wrapper"));
      updateJsonData();
    });

    // 「商材を削除」ボタン
    wrapper.on("click", ".remove-item-btn", function () {
      $(this).closest(".ranking-item").remove();
      updateJsonData();
    });

    // 通常フィールドの入力イベント
    wrapper.on("input change", ".item-field", function () {
      updateJsonData();
    });

    // 商材全体の並び替え
    wrapper.sortable({
      handle: ".item-handle",
      update: function (event, ui) {
        updateJsonData();
      },
    });

    // ---【ここからが新規追加部分】---

    // 「評価項目などを追加」ボタン
    wrapper.on("click", ".add-sub-item-btn", function () {
      const type = $(this).data("type");
      const $subWrapper = $(this).prev(".sub-items-wrapper");
      let template;
      if (type === "rating") template = ratingTemplateHtml;
      if (type === "spec") template = specTemplateHtml;
      if (type === "summary") template = summaryTemplateHtml;
      if (type === "comparison") template = comparisonTemplateHtml;

      if (template) {
        $subWrapper.append(template);
        updateJsonData();
      }
    });

    // 「評価項目などを削除」ボタン
    wrapper.on("click", ".remove-sub-item-btn", function () {
      $(this).closest(".sub-item").remove();
      updateJsonData();
    });

    // サブ項目のフィールド入力イベント
    wrapper.on("input change", ".sub-item-field", function () {
      updateJsonData();
    });

    // サブ項目の並び替え機能を有効化する関数
    function enableSubItemSortable(selector) {
      selector.sortable({
        handle: ".sub-item-handle",
        update: function (event, ui) {
          updateJsonData();
        },
      });
    }

    // ---【ここまでが新規追加部分】---

    // =====================================================================
    // 初期化処理
    // =====================================================================
    renderItems();
    // 初期描画された項目に対してもサブ項目のソータブルを有効化
    enableSubItemSortable(wrapper.find(".sub-items-wrapper"));
  });
})(jQuery);
