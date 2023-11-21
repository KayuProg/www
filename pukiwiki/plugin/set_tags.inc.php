<?php
/**
 * タグ付けされている情報を表示する
 *
 * @desc タグ付け自体はこのプラグイン無しでも可能
 * @release_version 1.6
 * @author kjirou <kjirou.web[at-mark]gmail.com>
 *                <http://kjirou.sakura.ne.jp/mt/>
 * @license MIT License http://www.opensource.org/licenses/mit-license.php
 */
require_once PLUGIN_DIR . "generate_tags.inc.php";

function plugin_set_tags_convert()
{
    global $vars;

    $tags = PluginGenerateTags_TagManager::pick($vars["page"]);

    // タグリレーション機能を使用するか否かのフラグ
    if (!!count(get_source(PLUGIN_GENERATE_TAGS_TAGRELATION_CONFIG_PAGENAME))) {
        $tags = PluginGenerateTags_TagManager::append_tag_by_tagrelation($tags
            , PluginGenerateTags_TagManager::get_tagrelations());
    }
    sort($tags);

    $html = '';
    $html .= '<div>Tag:[';
    $c = 0;
    foreach ($tags as $tag) {
        if ($c > 0) $html .= ", ";
        $encoded = rawurlencode(PLUGIN_GENERATE_TAGS_TAG_PAGE_PREFIX . $tag);
        $html .= '<a href="' . get_script_uri() . '?' . $encoded . '">';
        $html .= $tag . '</a>';
        $c++;
    }
    $html .= ']</div>';

    return $html;
}
?>
