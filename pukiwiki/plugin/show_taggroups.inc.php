<?php
/**
 * タググループを表示する
 *
 * @desc 第1引数により表示方法を選択できる。
 *       選択が無い場合は default を選択する。
 *       default  = 通常表示
 *       list     = 縦長のリスト表示
 * @desc 第2引数により、文字の大きさを指定できる
 *       default = 設定無し
 *       整数    = pxにてサイズ指定
 * @release_version 1.4
 * @author kjirou <kjirou.web[at-mark]gmail.com>
 *                <http://kjirou.sakura.ne.jp/mt/>
 * @license MIT License http://www.opensource.org/licenses/mit-license.php
 */
require_once PLUGIN_DIR . "generate_tags.inc.php";

function plugin_show_taggroups_convert(){
    global $vars, $get;

    $tm =& new PluginGenerateTags_TagManager;
    $summary = $tm->get_summary();
    $tags = $summary["tags_to_pages"];
    $taggroups = $summary["taggroups_to_tags"];
    ksort($taggroups);

    $args = func_get_args();

    // 各タググループリンクへのスタイル
    $styles = array();
    foreach ($taggroups as $k => $v) {
        $style = 'style="';
        if (is_numeric($args[1])) $style .= "font-size:" . $args[1] . "px;";
        $style .= '" ';
        $styles[$k] = $style;
    }

    $html = '';
    // 通常表示
    if ($args[0] === "default" || count($args) === 0) {
        $html .= '<div>';
        $c = 0;
        foreach ($taggroups as $k => $v) {
            if ($c > 0) $html .= " ";
            $html .= '<b><a href="' . get_script_uri() . '?' . rawurlencode(
                PLUGIN_GENERATE_TAGS_TAG_PAGE_PREFIX . PLUGIN_GENERATE_TAGS_TAGGROUP_PAGE_PREFIX . $k);
            $html .= '" ' . $styles[$k] . '>' . $k . '[' . count($taggroups[$k]) . ']</a></b>';
            $c++;
        }
        $html .= ' <a href="' . get_script_uri() . '?' . rawurlencode(PLUGIN_GENERATE_TAGS_TAGGROUP_CONFIG_PAGENAME);
        $html .= '" style="font-size:9px;" >edit</a>';
        $html .= '</div>';
    // 縦長リスト表示
    } else if ($args[0] === "list") {
        $html .= '<ul>';
        $c = 0;
        foreach ($taggroups as $k => $v) {
            if ($c > 0) $html .= " ";
            $html .= '<li><a href="' . get_script_uri() . '?' . rawurlencode(
                PLUGIN_GENERATE_TAGS_TAG_PAGE_PREFIX . PLUGIN_GENERATE_TAGS_TAGGROUP_PAGE_PREFIX . $k);
            $html .= '" ' . $styles[$k] . '>' . $k . '[' . count($taggroups[$k]) . ']</a></li>';
            $c++;
        }
        $html .= '</ul>';
        $html .= '<a href="' . get_script_uri() . '?' . rawurlencode(PLUGIN_GENERATE_TAGS_TAGGROUP_CONFIG_PAGENAME);
        $html .= '" style="font-size:9px;" >edit</a>';
    }
    return $html;
}
?>
