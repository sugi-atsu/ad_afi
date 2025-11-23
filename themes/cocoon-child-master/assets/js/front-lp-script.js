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
                <h2 class="wp-block-heading has-text-align-center lp-summary-ranking__title">【ITエンジニア】おすすめTOP${items.length}</h2>
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
                        <p class="has-text-align-center"><a href="${item.affiliateLink || "#"
        }">${item.productName || ""}</a></p>
                    </div>
                    <div class="wp-block-group ranking-item__features">${featuresHTML}</div>
                    <div class="wp-block-group ranking-item__cta">
                        <p><a class="summary-cta-button" href="${item.affiliateLink || "#"
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
          }" class="custom-cta-button" target="_blank" rel="noopener sponsored">公式サイトへ</a></td>`;
      });
      tableHTML += "</tr>";

      tableHTML +=
        '</tbody></table><figcaption class="wp-element-caption">横にスクロールできます</figcaption></figure>';
      tableContainer.innerHTML = tableHTML;
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
                        <div class="product-detail__title-wrapper">商材説明詳細</div>
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
          }" class="custom-cta-button ranking-card__cta-button" target="_blank" rel="noopener sponsored">${item.ctaButtonText || item.productName + "で詳しく見てみる"
          }</a>
                    </div>
                </div>
            `;
      });
      rankingContainer.innerHTML = rankingHTML;
    }
  }
});
