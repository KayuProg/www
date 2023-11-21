<?php
/**
 * ���ƤΥ�����ɽ������
 *
 * @desc ��1�����ˤ��ɽ����ˡ������Ǥ��롣
 *       ����̵������ default �����򤹤롣
 *       default  = �̾�ɽ��
 *       list     = ��Ĺ�Υꥹ��ɽ��
 *       tagcloud = �������饦��ɽ��
 * @desc ��2�����ˤ�ꡢʸ�����礭�������Ǥ���
 *       default = ����̵��
 *       ����    = px�ˤƥ���������
 *                 �������饦��ɽ���ξ��ϡ��Ǥ⾮����ʸ�������������
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

    // tagcloud�ѥ��饹���󥹥��󥹤�����
    $tc = null;
    if ($args[0] === "tagcloud") {
        $tc =& new PluginShowTags_TagCloud;
        $tc->set_levels($tags);
    }

    // �ƥ�����󥯤ؤΥ�������
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
    // �̾�ɽ��
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
    // ��Ĺ�ꥹ��ɽ��
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
    // �������饦��ɽ��
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
 * �������饦�ɥ��饹
 *
 * @see http://kjirou.sakura.ne.jp/mt/2007/09/post_57.html
 */
class PluginShowTags_TagCloud {

    /**
     * ��������Ѳ���ꥹ��
     *
     * @var array
     */
    var $_counts = array();

    /**
     * ɽ����٥�ꥹ��
     *
     * @var array
     */
    var $_levels = array();


    /**
     * ���󥹥ȥ饯��
     */
    function PluginShowTags_TagCloud(){
    }

    /**
     * �ƥ�����ɽ����٥�����ꤹ��
     *
     * @param array $pages _convert_pages_to_counts����
     */
    function set_levels($pages){
        $this->_convert_pages_to_counts($pages);
        $this->_calculate_levels();
    }

    /**
     * �����ȥڡ���̾�ꥹ�ȤΥ��åȤ򥿥�������Ѳ���Υ��åȤΥꥹ�Ȥ��Ѵ�����
     *
     * @see PluginGenerateTags_TagManager::get
     * @param array $pages �����ȥڡ���̾�ꥹ�ȤΥ��åȤΥꥹ��
     */
    function _convert_pages_to_counts($pages){
        $this->_counts = array();
        foreach ($pages as $k => $v) {
            $this->_counts[$k] = count($v);
        }
    }

    /**
     * �ƥ�����ɽ����٥�򻻽Ф���
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
     * $_levels�Υ�������
     */
    function levels(){
        return $this->_levels;
    }
}
?>
