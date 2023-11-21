<?php
/**
 * �������롼�פ�ɽ������
 *
 * @desc ��1�����ˤ��ɽ����ˡ������Ǥ��롣
 *       ����̵������ default �����򤹤롣
 *       default  = �̾�ɽ��
 *       list     = ��Ĺ�Υꥹ��ɽ��
 * @desc ��2�����ˤ�ꡢʸ�����礭�������Ǥ���
 *       default = ����̵��
 *       ����    = px�ˤƥ���������
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

    // �ƥ������롼�ץ�󥯤ؤΥ�������
    $styles = array();
    foreach ($taggroups as $k => $v) {
        $style = 'style="';
        if (is_numeric($args[1])) $style .= "font-size:" . $args[1] . "px;";
        $style .= '" ';
        $styles[$k] = $style;
    }

    $html = '';
    // �̾�ɽ��
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
    // ��Ĺ�ꥹ��ɽ��
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
