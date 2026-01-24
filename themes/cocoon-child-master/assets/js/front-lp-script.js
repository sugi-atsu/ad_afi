// DOMの読み込みが完了したら実行
document.addEventListener("DOMContentLoaded", function () {
  // PHPから渡されたデータを取得
  const items = window.rankingLpData?.items || [];

  // ===================================
  // 機能0：動的サマリーランキング【変更なし】
  // ===================================
  const summaryContainer = document.getElementById(
    "dynamic-summary-ranking-app"
  );
  if (summaryContainer && items.length > 0) {
    let summaryHTML = `
            <div class="wp-block-group lp-summary-ranking">
                <h2 class="wp-block-heading has-text-align-center lp-summary-ranking__title">${window.rankingLpData?.sectionTitle || ""}</h2>
                <div class="wp-block-group lp-summary-ranking__body">
        `;
    items.forEach((item, index) => {
      const rank = index + 1;

      let featuresHTML = "";
      if (Array.isArray(item.summaryPoints) && item.summaryPoints.length > 0) {
        featuresHTML =
          "<ul>" +
          item.summaryPoints.map((p) => `<li>${p.point || ""}</li>`).join("") +
          "</ul>";
      }

      // ランキングバッジのHTMLを生成
      let badgeHTML = "";
      if (rank <= 3) {
        // 1位から3位までは画像を使用
        badgeHTML = `<img src="https://cp01.rescareer.com/wp-content/uploads/2025/11/rank${rank}.png" alt="${rank}位" class="ranking-badge-image" />`;
      } else {
        // 4位以降はテキスト
        badgeHTML = `<span class="rank-text">${rank}位</span>`;
      }

      summaryHTML += `
                <div class="wp-block-group lp-summary-ranking__item">
                    <div class="ranking-badge-summary rank-${rank}">${badgeHTML}</div>
                    <div class="wp-block-group ranking-item__logo-area">
                        <figure class="wp-block-image"><img src="${item.imageUrl || ""
        }" alt="${item.productName || ""} ロゴ"></figure>
                    </div>
                    <div class="ranking-item__content-wrapper">
                        <p class="has-text-align-center"><a href="${item.affiliateLink || "#"
        }">${item.productName || ""}</a></p>
                        <div class="wp-block-group ranking-item__features">${featuresHTML}</div>
                    </div>
                    <div class="wp-block-group ranking-item__cta">
                        <p><a class="summary-cta-button lp-track-cta" href="${item.affiliateLink || "#"
        }">公式<br>サイト</a></p>
                    </div>
                </div>
            `;
    });
    summaryHTML += "</div></div>";
    summaryContainer.innerHTML = summaryHTML;
  } else if (summaryContainer) {
    summaryContainer.innerHTML = "<p>表示するランキングがありません。</p>";
  }

  /* タイトルの書き換え処理（比較表） */
  const comparisonTitleElement = document.querySelector(".lp-comparison-title");
  if (
    comparisonTitleElement &&
    window.rankingLpData?.comparisonTitle &&
    window.rankingLpData.comparisonTitle.trim() !== ""
  ) {
    comparisonTitleElement.textContent = window.rankingLpData.comparisonTitle;
  }

  // ===================================
  // 機能1：動的比較表【変更なし】
  // ===================================
  const tableContainer = document.getElementById(
    "dynamic-comparison-table-app"
  );
  if (tableContainer) {
    if (items.length === 0) {
      tableContainer.innerHTML = "<p>比較する商品がありません。</p>";
    } else {
      const renderStars = (rating) => {
        let stars = "";
        const numRating = parseFloat(rating);
        if (isNaN(numRating)) return "評価なし";
        for (let i = 1; i <= 5; i++) {
          stars += i <= numRating ? "★" : "☆";
        }
        return `${stars} (${numRating.toFixed(1)})`;
      };

      let tableHTML = '<figure class="wp-block-table"><table>';
      tableHTML += "<thead><tr><th></th>";
      items.forEach((item) => {
        tableHTML += `<th>${item.productName || ""}</th>`;
      });
      tableHTML += "</tr></thead>";
      tableHTML += "<tbody>";

      const allLabels = new Set();
      items.forEach((item) => {
        if (Array.isArray(item.ratingItems))
          item.ratingItems.forEach((r) => allLabels.add(r.label));
        if (Array.isArray(item.specItems))
          item.specItems.forEach((s) => allLabels.add(s.label));
      });
      allLabels.add("総合評価");

      allLabels.forEach((label) => {
        let rowHTML = `<tr><td>${label}</td>`;
        items.forEach((item) => {
          let cellValue = "-";
          if (label === "総合評価") {
            cellValue = renderStars(item.overallRating);
          } else {
            const ratingItem = Array.isArray(item.ratingItems)
              ? item.ratingItems.find((r) => r.label === label)
              : null;
            const specItem = Array.isArray(item.specItems)
              ? item.specItems.find((s) => s.label === label)
              : null;

            if (ratingItem) {
              cellValue = renderStars(ratingItem.value);
            } else if (specItem) {
              cellValue = specItem.value;
            }
          }
          rowHTML += `<td>${cellValue}</td>`;
        });
        rowHTML += "</tr>";
        tableHTML += rowHTML;
      });

      tableHTML += "<tr><td></td>";
      items.forEach((item) => {
        tableHTML += `<td><a href="${item.affiliateLink || "#"
          }" class="custom-cta-button" target="_blank" rel="noopener sponsored"><span>公式サイトへ</span></a></td>`;
      });
      tableHTML += "</tr>";

      tableHTML +=
        '</tbody></table><figcaption class="wp-element-caption">横にスクロールできます</figcaption></figure>';
      tableContainer.innerHTML = tableHTML;
    }
  }

  // ===================================
  // 機能3：動的比較カード（横スクロール比較表）
  // ===================================
  const comparisonCardsContainer = document.getElementById(
    "dynamic-comparison-cards-app"
  );
  if (comparisonCardsContainer) {
    if (items.length === 0) {
      comparisonCardsContainer.innerHTML = "<p>比較する商品がありません。</p>";
    } else {
      // 共通関数: 星評価レンダリング
      const renderStars = (rating) => {
        let stars = "";
        const numRating = parseFloat(rating);
        if (isNaN(numRating)) return "評価なし";
        for (let i = 1; i <= 5; i++) {
          stars += i <= numRating ? "★" : "☆";
        }
        return stars;
      };

      // 共通関数: 判定アイコンクラス取得
      const getStatusClass = (status) => {
        switch (status) {
          case 'double-circle': return 'status-double-circle';
          case 'circle': return 'status-circle';
          case 'triangle': return 'status-triangle';
          case 'cross': return 'status-cross';
          default: return 'status-none';
        }
      };

      // 1. 全商材からユニークな比較項目キーを取得（スペック、比較、評価の順）
      const specLabelSet = new Set();
      const compLabelSet = new Set();
      const ratingLabelSet = new Set();

      items.forEach((item) => {
        if (Array.isArray(item.specItems)) item.specItems.forEach((s) => specLabelSet.add(s.label));
        if (Array.isArray(item.comparisonItems)) item.comparisonItems.forEach((c) => compLabelSet.add(c.label));
        if (Array.isArray(item.ratingItems)) item.ratingItems.forEach((r) => ratingLabelSet.add(r.label));
      });

      // 表示用の全ラベルリスト
      // 表示用の全ラベルリスト（重複排除、"項目名"を除外）
      const mergedLabels = [
        ...Array.from(specLabelSet),
        ...Array.from(compLabelSet),
        ...Array.from(ratingLabelSet)
      ];
      const comparisonLabels = Array.from(new Set(mergedLabels))
        .filter(label => label !== '項目名');

      // HTML構築開始
      let html = '<div class="comparison-container">';

      // 2. 左端インデックスカラムの生成
      html += '<div class="comparison-index-column">';
      // ヘッダーセル（空またはタイトル）
      html += '<div class="comparison-index-cell header-cell">おすすめ<br>ランキング</div>';
      // 総合評価ラベル
      html += '<div class="comparison-index-cell comparison-rating-cell">総合評価</div>';
      // 各比較項目のラベル
      comparisonLabels.forEach(label => {
        html += `<div class="comparison-index-cell">${label}</div>`;
      });
      // CTA行のラベル（空でも可）
      html += '<div class="comparison-index-cell comparison-cta-cell">公式サイト</div>';
      html += '</div>'; // end index-column

      // 3. 各商材カラムの生成
      items.forEach((item, index) => {
        // 1番目のアイテムにおすすめクラスを付与
        const recommendedClass = index === 0 ? 'is-recommended' : '';
        html += `<div class="comparison-product-column ${recommendedClass}">`;

        // ヘッダー（画像・リンク）
        html += `
          <div class="comparison-product-header">
            <div class="comparison-product-logo">
              <a href="${item.affiliateLink || '#'}" target="_blank" rel="noopener sponsored">
                <img src="${item.imageUrl || ''}" alt="${item.productName || ''}">
              </a>
            </div>
            <div class="comparison-product-name">
              <a href="${item.affiliateLink || '#'}" target="_blank" rel="noopener sponsored">${item.productName || ''}</a>
            </div>
          </div>
        `;

        // 総合評価セル
        const numRating = parseFloat(item.overallRating) || 0;
        html += `
          <div class="comparison-cell comparison-rating-cell">
            <span class="rating-score-large">${numRating.toFixed(1)}</span>
            <span class="rating-stars">${renderStars(numRating)}</span>
          </div>
        `;

        // 各比較項目セル
        comparisonLabels.forEach(label => {
          let status = 'none';
          let text = '-';
          let isRatingValue = false;
          let ratingValue = 0;

          // 優先順位: Comparison -> Spec -> Rating
          // ComparisonItemsから検索
          let compItem = Array.isArray(item.comparisonItems) ? item.comparisonItems.find(c => c.label === label) : null;
          if (compItem) {
            status = compItem.status;
            text = compItem.text || '';
          } else {
            // SpecItemsから検索
            let specItem = Array.isArray(item.specItems) ? item.specItems.find(s => s.label === label) : null;
            if (specItem) {
              text = specItem.value || '';
              // statusはnoneのまま
            } else {
              // RatingItemsから検索
              let ratingItem = Array.isArray(item.ratingItems) ? item.ratingItems.find(r => r.label === label) : null;
              if (ratingItem) {
                isRatingValue = true;
                ratingValue = parseFloat(ratingItem.value) || 0;
                text = ''; // テキストではなく星を表示する
              }
            }
          }

          if (isRatingValue) {
            // 評価項目の場合
            html += `
                <div class="comparison-cell">
                  <span class="rating-stars" style="font-size:14px">${renderStars(ratingValue)}</span>
                  <span style="font-size:12px">(${ratingValue.toFixed(1)})</span>
                </div>
              `;
          } else {
            // 通常項目（比較 or スペック）
            // statusがnoneでない場合はアイコンを表示
            let iconHtml = status !== 'none' ? `<span class="comparison-status-icon ${getStatusClass(status)}"></span>` : '';

            html += `
                <div class="comparison-cell">
                  ${iconHtml}
                  <span class="comparison-status-text">${text}</span>
                </div>
              `;
          }
        });

        // CTAセル
        html += `
          <div class="comparison-cell comparison-cta-cell">
             <a href="${item.affiliateLink || '#'}" class="comparison-cta-btn lp-track-cta" target="_blank" rel="noopener sponsored"><span>公式サイト</span></a>
          </div>
        `;

        html += '</div>'; // end product-column
      });

      html += '</div>'; // end comparison-container
      comparisonCardsContainer.innerHTML = html;

      // スクロールヒントの追加 (スマホ用)
      const hintHtml = `
        <div class="scroll-hint-overlay">
            <div class="scroll-hint-content">
                <span>横へスクロール</span>
                <div class="scroll-hint-icon"></div>
            </div>
        </div>
      `;

      // 既存のヒントがあれば削除（再描画時などの重複防止）
      const existingHint = comparisonCardsContainer.parentNode.querySelector('.scroll-hint-overlay');
      if (existingHint) existingHint.remove();

      // コンテナの後ろ（セクション内）に追加
      comparisonCardsContainer.insertAdjacentHTML('afterend', hintHtml);

      // スクロールまたはタッチでヒントを消す
      const hintEl = comparisonCardsContainer.parentNode.querySelector('.scroll-hint-overlay');
      if (hintEl) {
        const removeHint = () => {
          hintEl.style.opacity = '0';
          setTimeout(() => {
            if (hintEl.parentNode) hintEl.parentNode.removeChild(hintEl);
          }, 500);
        };

        comparisonCardsContainer.addEventListener('scroll', removeHint, { once: true });
        comparisonCardsContainer.addEventListener('touchstart', removeHint, { once: true });
      }
    }
  }

  // ===================================
  // 機能2：動的ランキング（詳細）【ここからが改修箇所】
  // ===================================
  const rankingContainer = document.getElementById("dynamic-ranking-app");
  if (rankingContainer) {
    // セクションタイトルの更新
    if (window.rankingLpData?.sectionTitle) {
      const sectionTitleEl = rankingContainer.closest('.lp-section')?.querySelector('.lp-section-title');
      if (sectionTitleEl) {
        sectionTitleEl.textContent = window.rankingLpData.sectionTitle;
      }
    }

    if (items.length === 0) {
      rankingContainer.innerHTML =
        "<p>ランキングに表示する商品がありません。</p>";
    } else {
      const renderStars = (rating) => {
        let stars = "";
        const numRating = parseFloat(rating);
        if (isNaN(numRating) || numRating <= 0) return "評価なし";
        for (let i = 1; i <= 5; i++) {
          stars += i <= numRating ? "★" : "☆";
        }
        return stars;
      };

      let rankingHTML = "";
      items.forEach((item, index) => {
        const rank = index + 1;

        // ランキングバッジのHTMLを生成
        let badgeHTML = "";
        if (rank <= 3) {
          // 1位から3位までは画像を使用
          badgeHTML = `<img src="https://cp01.rescareer.com/wp-content/uploads/2025/11/rank${rank}.png" alt="${rank}位" class="ranking-badge-image" />`;
        } else {
          // 4位以降はテキスト
          badgeHTML = `<span class="rank-text">${rank}位</span>`;
        }

        // ★★★ 修正箇所 START ★★★
        // 評価テーブルとスペックテーブルを別々のHTML文字列として生成
        // th/td を div に変更
        let ratingTableHTML = "";
        if (Array.isArray(item.ratingItems) && item.ratingItems.length > 0) {
          ratingTableHTML =
            '<div class="ranking-card__table ranking-card__rating-table"><div class="ranking-card__table-grid">';
          item.ratingItems.forEach((rItem) => {
            ratingTableHTML += `
                    <div class="ranking-card__table-item">
                        <div class="table-item__header">${rItem.label}</div>
                        <div class="table-item__data"><span class="stars">${renderStars(
              rItem.value
            )}</span></div>
                    </div>
                `;
          });
          ratingTableHTML += "</div></div>";
        }

        let specTableHTML = "";
        if (Array.isArray(item.specItems) && item.specItems.length > 0) {
          specTableHTML =
            '<div class="ranking-card__table ranking-card__spec-table"><div class="ranking-card__table-grid">';
          item.specItems.forEach((sItem) => {
            specTableHTML += `
                    <div class="ranking-card__table-item">
                        <div class="table-item__header">${sItem.label}</div>
                        <div class="table-item__data">${(
                sItem.value || ""
              ).replace(/\n/g, "<br>")}</div>
                    </div>
                `;
          });
          specTableHTML += "</div></div>";
        }
        // ★★★ 修正箇所 END ★★★

        rankingHTML += `
                <div class="wp-block-group lp-section">
                    <div class="ranking-card__header">
                        <div class="ranking-card__header-flex">
                            <div class="ranking-card__header-main">
                                <div class="ranking-badge rank-${rank}">${badgeHTML}</div>
                                <div class="product-title-group">
                                    <a href="${item.affiliateLink || "#"
          }"><h2 class="ranking-card__product-name">${item.productName || "商材名"
          }</h2></a>
                                    <h3 class="ranking-card__catchphrase">${item.catchphrase || ""
          }</h3>
                                </div>
                            </div>
                            <div class="ranking-card__header-sub">
                                <div class="ranking-card__overall-rating">
                                    <span class="ranking-card__overall-rating-label">総合評価</span>
                                    <span class="ranking-card__overall-rating-stars">${renderStars(
            item.overallRating
          )}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="ranking-card__main-content">
                        <div class="ranking-card__image-column">
                            <a href="${item.affiliateLink || "#"
          }" target="_blank" rel="noopener sponsored"><img src="${item.imageUrl || ""
          }" alt="${item.productName || ""}" /></a>
                        </div>
                        <div class="ranking-card__info-column">
                            ${ratingTableHTML}
                            ${specTableHTML}
                        </div>
                    </div>
                    
                    ${item.productDetail
            ? `
                    <div class="product-detail">
                        <div class="product-detail__title-wrapper">ここがポイント！</div>
                        <div class="product-detail__main-content">
                            <div class="product-detail__text-content">
                                <div class="product-detail__title">${item.productDetailTitle || ""
            }</div>
                                <p class="product-detail__text">${(
              item.productDetail || ""
            ).replace(/\n/g, "<br>")}
                                </p>
                            </div>
                        </div>
                    </div>`
            : ""
          }

                    <div class="ranking-card__footer">
                        <p class="ranking-card__cta-microcopy">${item.ctaMicrocopy || ""
          }</p>
                        <a href="${item.affiliateLink || "#"
          }" class="custom-cta-button ranking-card__cta-button lp-track-cta" target="_blank" rel="noopener sponsored"><span>${item.ctaButtonText || item.productName + "で詳しく見てみる"
          }</span></a>
                    </div>
                </div>
            `;
      });
      rankingContainer.innerHTML = rankingHTML;
    }
  }
});
