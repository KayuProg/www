<?php
/**
 * 全てのタグを表示する
 *
 * @desc 第1引数により表示方法を選択できる。
 *       選択が無い場合は default を選択する。
 *       default  = 通常表示
 *       list     = 縦長のリスト表示
 *       tagcloud = タグクラウド表示
 * @desc 第2引数により、文字の大きさを指定できる
 *       default = 設定無し
 *       整数    = pxにてサイズ指定
 *                 タグクラウド表示の場合は、最も小さい文字サイズを指定
 * @release_version 1.4
 * @author kjirou <kjirou.web[at-mark]gmail.com>
 *                <http://kjirou.sakura.ne.jp/mt/>
 * @license MIT License http://www.opensource.org/licenses/mit-license.php
 */
require_once PLUGIN_DIR . "generate_tags.inc.php";

function plugin_show_tags_convert(){
    global $vars, $get;

    $tm =& new PluginGenerateTags_TagManager;
    $summary = $tm->get_summary();
    $tags = $summary["tags_to_pages"];
    if (count($tags) === 0) return "";
    ksort($tags);

    $args = func_get_args();

    // tagcloud用クラスインスタンスの生成
    $tc = null;
    if ($args[0] === "tagcloud") {
        $tc =& new PluginShowTags_TagCloud;
        $tc->set_levels($tags);
    }

    // 各タグリンクへのスタイル
    $styles = array();
    foreach ($tags as $k => $v) {
        $style = 'style="';
        if ($args[0] === "tagcloud") {
            $levels = $tc->levels();
            $base_font_size = (is_numeric($args[1])) ? $args[1]: 10;
            $style .= "font-size:" . ($levels[$k] + $base_font_size) . "px;";
        } else {
            if (is_numeric($args[1])) $style .= "font-size:" . $args[1] . "px;";
        }
        $style .= '" ';
        $styles[$k] = $style;
    }

    $html = '';
    // 通常表示
    if ($args[0] === "default" || count($args) === 0) {
        $html .= '<div>';
        $c = 0;
        foreach ($tags as $k => $v) {
            if ($c > 0) $html .= " ";
            $html .= '<b><a href="' . get_script_uri() . '?' . rawurlencode(PLUGIN_GENERATE_TAGS_TAG_PAGE_PREFIX . $k);
            $html .= '" ' . $styles[$k] . '>' . $k . '(' . count($v) . ')</a></b>';
            $c++;
        }
        $html .= '</div>';
    // 縦長リスト表示
    } else if ($args[0] === "list") {
        $html .= '<ul>';
        $c = 0;
        foreach ($tags as $k => $v) {
            if ($c > 0) $html .= " ";
            $html .= '<li><a href="' . get_script_uri() . '?' . rawurlencode(PLUGIN_GENERATE_TAGS_TAG_PAGE_PREFIX . $k);
            $html .= '" ' . $styles[$k] . '>' . $k . '(' . count($v) . ')</a></li>';
            $c++;
        }
        $html .= '</ul>';
    // タグクラウド表示
    } else if ($args[0] === "tagcloud") {
        $html .= '<div>';
        $c = 0;
        foreach ($tags as $k => $v) {
            if ($c > 0) $html .= " ";
            $html .= '<a href="' . get_script_uri() . '?' . rawurlencode(PLUGIN_GENERATE_TAGS_TAG_PAGE_PREFIX . $k);
            $html .= '" ' . $styles[$k] . '>' . $k . '</a>';
            $c++;
        }
        $html .= '</div>';
    }
    return $html;
}

/**
 * タグクラウドクラス
 *
 * @see http://kjirou.sakura.ne.jp/mt/2007/09/post_57.html
 */
class PluginShowTags_TagCloud {

    /**
     * タグ被使用回数リスト
     *
     * @var array
     */
    var $_counts = array();

    /**
     * 表示レベルリスト
     *
     * @var array
     */
    var $_levels = array();


    /**
     * コンストラクタ
     */
    function PluginShowTags_TagCloud(){
    }

    /**
     * 各タグの表示レベルを設定する
     *
     * @param array $pages _convert_pages_to_counts参照
     */
    function set_levels($pages){
        $this->_convert_pages_to_counts($pages);
        $this->_calculate_levels();
    }

    /**
     * タグとページ名リストのセットをタグと被使用回数のセットのリストへ変換する
     *
     * @see PluginGenerateTags_TagManager::get
     * @param array $pages タグとページ名リストのセットのリスト
     */
    function _convert_pages_to_counts($pages){
        $this->_counts = array();
        foreach ($pages as $k => $v) {
            $this->_counts[$k] = count($v);
        }
    }

    /**
     * 各タグの表示レベルを算出する
     */
    function _calculate_levels(){

        $this->_levels = array();

        $tmp = $this->_counts;
        sort($tmp);
        $min = (float)(sqrt($tmp[0]));
        $max = (float)(sqrt($tmp[count($tmp) - 1]));
        if ($max - $min == 0) {
            $factor = 1;
        } else {
            $factor = 24 / ($max - $min);
        }

        foreach ($this->_counts as $k => $v) {
            $this->_levels[$k] = (int)(ceil((sqrt($v) - $min) * $factor));
        }
    }

    /**
     * $_levelsのアクセサ
     */
    function levels(){
        return $this->_levels;
    }
}
?>
