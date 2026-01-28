<?php
// WordPressのブロックパターンキャッシュ(Transient)をクリアするスクリプト
require_once('../../../wp-load.php'); // 適宜パス調整

if (function_exists('delete_transient')) {
    $deleted = delete_transient('auto_registered_patterns');
    echo "Pattern cache deleted: " . ($deleted ? "Yes" : "No (or not found)") . "\n";
    
    // 他の可能性のあるキャッシュも削除
    global $wpdb;
    $wpdb->query("DELETE FROM $wpdb->options WHERE option_name LIKE '_transient_auto_registered_patterns_%'");
    $wpdb->query("DELETE FROM $wpdb->options WHERE option_name LIKE '_transient_timeout_auto_registered_patterns_%'");
    echo "Additional transients cleared from DB.\n";
} else {
    echo "WP functions not found.\n";
}
unlink(__FILE__); // 実行後に自分自身を削除
