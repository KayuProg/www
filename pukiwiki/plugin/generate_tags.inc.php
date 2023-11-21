<?php
/**
 * �ƥڡ��������ĥ�����ޤȤ�ƥե��������¸����
 *
 * @desc ":config/plugin/generate_tags/taggroups"�Ȥ����ڡ���̾��PukiWiki�ڡ�������������Ƥ����硢
 *       �����Υ��롼�ԥ󥰵�ǽ��ͭ���ˤʤ롣
 *       �ڡ�����ˤϰʲ��Τ褦�ˡ�PukiWiki��ˡ�Υơ��֥��Ȥߤ�Ȥä����ꤹ�롣
 *       |�������롼��̾1|����̾1,����̾2,����̾3|
 *       |�������롼��̾2|����̾3,����̾4,����̾5|
 * @desc ":config/plugin/generate_tags/tagrelations"�Ȥ����ڡ���̾��PukiWiki�ڡ�������������Ƥ����硢
 *       �����Υ�졼�����ǽ��ͭ���ˤʤ롣
 *       �ڡ�����ˤϰʲ��Τ褦�ˡ�PukiWiki��ˡ�Υơ��֥��Ȥߤ�Ȥä����ꤹ�롣
 *       |centos,ubuntu,freebsd|linux       |
 *       |actionscript,flex    |flash       |
 *       |flex                 |actionscript|
 *       ��centos �� ubuntu �� freebsd ������Ĥ������� linux ��������ưŪ�˿�����
 *       ���Ƶ�Ū��Ƚ�ǤϤ��ʤ����㤨��
 *         |a|b|
 *         |b|c|
 *         ���ΤȤ��ˤ� "a������ʤ�b��������" "b������ʤ�c��������" ��
 *         "a������ʤ�c��������"�ˤϤʤ�ʤ�
 * @desc ��1������ɽ���˴ؤ��륪�ץ��������Ǥ���
 *       "&"���ڤ�ǹ��ܤ�ʬ�䤹�롣���Ϥξܺ٤� plugin_generate_tags_parse_option_arg�򻲾�
 *       ����Ǥ��륪�ץ����ϰʲ����̤�
 *       "name=̾��"   -> ���������ܥ����"̾��"�Υ�٥���ѹ�����
 *       "tagrelation" -> ������졼���������ڡ����ؤΥ�󥯤�ܥ���β��˽��Ϥ���
 * @desc ��2���������������˴ؤ��륪�ץ��������Ǥ���
 *       "&"���ڤ�ǹ��ܤ�ʬ�䤹�롣���Ϥξܺ٤� plugin_generate_tags_parse_option_arg�򻲾�
 *       ����Ǥ��륪�ץ����ϰʲ����̤�
 *       "with-truncate"
 *         -> �������������롼�ץڡ����������ϡ��ѹ��Τ��ä��ڡ����Τߤ�ʬ�����ǹԤäƤ��뤬
 *            ���Υ��ץ�����Ĥ��뤳�Ȥ��ѹ���̵���ڡ�����ޤ�����Ƥ����������ե��ˤʤ�
 *            �������������롼�׾��󤬤ʤ�餫����ͳ���˲����줿�Ȥ��ˡ����٤������ꤷ�Ƽ¹Ԥ���
 * @release_version 1.7
 * @author kjirou <kjirou.web[at-mark]gmail.com>
 *                <http://kjirou.sakura.ne.jp/mt/>
 * @license MIT License http://www.opensource.org/licenses/mit-license.php
 */
define("PLUGIN_GENERATE_TAGS_TAGGING_PLUGIN_NAME", "set_tags");
define("PLUGIN_GENERATE_TAGS_TAG_PAGE_PREFIX", "tag/");
define("PLUGIN_GENERATE_TAGS_TAGGROUP_PAGE_PREFIX", "taggroup/");
/** �Ƽ�������¸����ե�����̾��cache�ǥ��쥯�ȥ����¸���� */
define("PLUGIN_GENERATE_TAGS_SUMMARY_STORAGE_FILENAME", "plugin_generate_tags_summary_storage.txt");
/** �������롼�׵�ǽ��ͭ���ˤ�����˺�����������ե������PukiWiki�ڡ���̾ */
define("PLUGIN_GENERATE_TAGS_TAGGROUP_CONFIG_PAGENAME", ":config/plugin/generate_tags/taggroups");
/** ������졼�����ǽ��ͭ���ˤ�����˺�����������ե������PukiWiki�ڡ���̾ */
define("PLUGIN_GENERATE_TAGS_TAGRELATION_CONFIG_PAGENAME", ":config/plugin/generate_tags/tagrelations");

function plugin_generate_tags_action()
{
    global $post, $vars;

    #// ��롦ǧ�ڤΥ����å�
    #check_editable($post['page'], true, true);

    // ��������ν��󡦥������������롼������
    $tm =& new PluginGenerateTags_TagManager;
    if (array_key_exists("with-truncate", $post) && !!$post["with-truncate"]) {
        $tm->set_with_truncate(true);
    }
    $tm->generate();

    // ������쥯��
    $vars["refer"] = $vars["page"];
    return array("msg" => "", "body" => "");
}

function plugin_generate_tags_convert()
{
    global $vars;

    $args = func_get_args();
    $vopts = plugin_generate_tags_parse_option_arg($args[0]);
    $gopts = plugin_generate_tags_parse_option_arg($args[1]);

    // ���֥ߥåȥܥ���̾���ѹ�
    $btn_name = "Generate tags";
    if (array_key_exists("name", $vopts) && strlen($vopts["name"]) > 0) {
        $btn_name = $vopts["name"];
    }

    $html = '';
    $html .= '<form action="' . get_script_uri() . '" method="post" style="margin:0;">';
    $html .= '<input type="hidden" name="page" value="' . htmlspecialchars($vars['page'], ENT_QUOTES) . '" />';
    // "with-truncate"���ץ����
    if (array_key_exists("with-truncate", $gopts)) {
        $html .= '<input type="hidden" name="with-truncate" value="1" />';
    }
    $html .= '<input type="hidden" name="plugin" value="generate_tags" />';
    $html .= '<input type="submit" value="' . $btn_name . '" />';
    // ������졼���������ե�����ؤΥ�󥯤�Ф���
    if (array_key_exists("tagrelation", $vopts)) {
        $encoded = rawurlencode(PLUGIN_GENERATE_TAGS_TAGRELATION_CONFIG_PAGENAME);
        $html .= ' <a href="' . get_script_uri() . '?' . $encoded . '" style="font-size:9px;">edit tagrelation</a>';
    }
    $html .= '</form>';

    return $html;
}

/**
 * ���ץ��������ΰ���ʸ�������Ϥ���
 *
 * @example
 *     "a=1&bb=22&ccc=333" -> array("a" => "1", "bb" => "22", "ccc" => "333")
 *     "name=��������&tagrelation" -> array("name" => "��������", "tagrelation" => "")
 * @param str $arg ���ץ��������ΰ��������ߤ�PukiWiki�ץ饰������1��2��������������
 * @return array ���ץ����ꥹ��
 */
function plugin_generate_tags_parse_option_arg($arg) {
    $options = array();
    foreach (explode('&', (string)$arg) as $v) {
        $kv = explode('=', $v, 2);
        $options[$kv[0]] = (string)$kv[1];
    }
    return $options;
}

/**
 * �����������饹
 */
class PluginGenerateTags_TagManager {

    /**
     * ��������ꥹ��
     *
     * @example
     *     array(
     *         "����̾1" => array("�ڡ���̾1", "�ڡ���̾2", "�ڡ���̾3"),
     *         "����̾2" => array("�ڡ���̾1", "�ڡ���̾3"),
     *         "����̾3" => array("�ڡ���̾2", "�ڡ���̾3"),
     *     );
     * @var array
     */
    var $_tags_to_pages = array();
    var $_pre_tags_to_pages = array(); // ����Υǡ���

    /**
     * �������롼�׾���ꥹ��
     *
     * @example
     *     array(
     *         "�������롼��̾1" => array("����̾1", "����̾2", "����̾3"),
     *         "�������롼��̾2" => array("����̾1", "����̾3"),
     *         "�������롼��̾3" => array("����̾2", "����̾3"),
     *     );
     * @var array
     */
    var $_taggroups_to_tags = array();
    var $_pre_taggroups_to_tags = array(); // ����Υǡ���

    /**
     * ������졼��������ꥹ��
     *
     * @example
     *     array(
     *         "����̾A" => array("����̾1", "����̾2", "����̾3"),
     *         "����̾B" => array("����̾1", "����̾3"),
     *     );
     *     ������̾1 �� ����̾2 �� ����̾3 ��Ĥ������ϡ�����̾A����ưŪ�˿�����
     *     ������̾1 �� ����̾3 ��Ĥ������ϡ�����̾B����ưŪ�˿�����
     * @var array
     */
    var $_tagrelations = array();

    /** �������������롼���������˺�ʬ�����ǤϤʤ����������ǹԤ��� */
    var $_with_truncate = false;


    function PluginGenerateTags_TagManager(){
    }

    /**
     * �����˴ؤ��Ƽ�������������
     */
    function generate() {

        // ���������ξ��ϥ��ȥ졼���ե������������
        $strage_filepath = CACHE_DIR . PLUGIN_GENERATE_TAGS_SUMMARY_STORAGE_FILENAME;
        if ($this->_with_truncate && file_exists($strage_filepath)) unlink($strage_filepath);

        // ������¸�����ǡ�������ǡ����Ȥ����ݻ�
        $summary = $this->get_summary();
        $this->_pre_tags_to_pages = $summary["tags_to_pages"];
        $this->_pre_taggroups_to_tags = $summary["taggroups_to_tags"];

        // �������롼�׵�ǽ����Ѥ��뤫�ݤ��Υե饰
        $use_taggroup = !!count(get_source(PLUGIN_GENERATE_TAGS_TAGGROUP_CONFIG_PAGENAME));

        $this->_set_tags_to_pages();
        if ($use_taggroup) $this->_set_taggroups_to_tags();
        $this->_save_summary();
        $this->_delete_tag_pages();
        $this->_generate_tag_pages();
        if ($use_taggroup) $this->_generate_taggroup_pages();
    }

    /**
     * �Ƽ����ꥹ�Ȥ򥹥ȥ졼�������������
     *
     * @return array
     *           "tags_to_pages"        : "����̾" => array("�ڡ���̾1", "�ڡ���̾2")
     *                                    �Υꥹ�Ȥ��ޤޤ�Ƥ�������
     *           "taggroups_to_tags"    : "�������롼��̾" => array("����̾1", "����̾2")
     *                                    �Υꥹ�Ȥ��ޤޤ�Ƥ�������
     *           *�ե����뤬̵�����ϡ����줾�����Ȥ�������ˤʤ�
     */
    function get_summary() {
        $filepath = CACHE_DIR . PLUGIN_GENERATE_TAGS_SUMMARY_STORAGE_FILENAME;
        $result = array(
            "tags_to_pages" => array(),
            "taggroups_to_tags" => array(),
        );
        if (file_exists($filepath)) $result = unserialize(file_get_contents($filepath));
        return $result;
    }

    /**
     * PukiWiki������������ꤵ��Ƥ��륿���Υꥹ�Ȥ��������
     *
     * @desc
     *     PukiWiki��������˵��Ҥ���Ƥ���ʲ��Τ褦�ʥץ饰�������Ϥ�
     *     #set_tags(���Ȥ��ɤ�,php,javascript) ��
     *     array("���Ȥ��ɤ�", "php", "javascript") ���֤���
     * @desc
     *     �����ϥ���޶��ڤ��ʬ�䤵�졢���������Ⱦ�Ѷ���ϥȥ�व���
     *     #set_tags(   aaa,   bbb,     c cc   ) -> array("aaa", "bbb", "c cc")
     *     Ϣ³��������ޤ�ȥ�व���
     *     #set_tags(aaa, ,   ,  , bbb, ccc) -> array("aaa", "bbb", "ccc")
     *     Ⱦ����ʸ���ѻ��Ͼ�ʸ�����Ѵ������
     *     #set_tags(AAA) -> array("aaa")
     * @param str $page PukiWiki�ڡ���̾
     * @return array �����Υꥹ��
     */
    function pick($page){
        $tags = array();
        foreach (get_source($page) as $line) {
            $line = preg_replace('/[\r\n]*$/', '', $line);
            $p = '/^# *' . PLUGIN_GENERATE_TAGS_TAGGING_PLUGIN_NAME . ' *\(([^)]*?)\).*$/';
            $tags_str = preg_replace($p, '$1', $line);
            if ($line === $tags_str) {
                continue;
            } else {
                // "#set_tags()", "#set_tags(  )" ������Ȥ����λ��꤬���ä����
                // �����ǤϤ����ʤ��� "tag/" �Υڡ���������Ƥ��ޤ�
                if ($tags_str === "") continue;

                $tags_str = preg_replace('/^ *(.*?) *$/', '$1', $tags_str);
                $tags = split(' *,( *, *)* *', $tags_str);
                foreach ($tags as $k => $v) $tags[$k] = strtolower($v);
                break;
            }
        }
        return $tags;
    }

    /**
     * ������졼���������ڡ����ξ�����������
     *
     * @see $_tagrelations
     * @desc PukiWiki�������β��ϤˤĤ��Ƥ�_set_taggroups_to_tags�˽ऺ��
     * @desc taggroup�Ǥ�set��get��ʬ���Ƥ��ʤ��Τˤ��ä���ʬ���Ƥ���Τ�
     *       set_tags.inc.php�Ǥ�����ʬ�����Ȥ�����
     */
    function get_tagrelations(){
        $tagrelations = array();
        foreach (get_source(PLUGIN_GENERATE_TAGS_TAGRELATION_CONFIG_PAGENAME) as $row) {
            if (preg_match('/^\\| *([^|]+?) *\\| *([^|]+?) *\\|/', $row, $matches))
                $tagrelations[$matches[2]] = split(' *, *', $matches[1]);
        }
        return $tagrelations;
    }

    /**
     * ������졼��������ˤ�꥿�����ɲä���
     *
     * @param arr $tags         1�ڡ��������ꤵ��Ƥ��륿���ꥹ��
     * @param arr $tagrelations get_tagrelations�Ǽ����Ǥ����Τ�����
     *                          �����ѿ���Ȥ��ʤ�=��Ū�������Ѥ��ߤ�������
     * @return arr
     */
    function append_tag_by_tagrelation($tags, $tagrelations = null){
        $trs = array();
        if ($tagrelations !== null) {
            $trs = $tagrelations;
        } else {
            $trs = $this->_tagrelations;
        }
        foreach ($tags as $tag) {
            foreach ($trs as $to => $froms) {
                if (in_array($tag, $froms, true)) array_push($tags, $to);
            }
        }
        return array_unique($tags);
    }

    /** setter: $_with_truncate */
    function set_with_truncate($val){
        $this->_with_truncate = $val;
    }

    /**
     * ��������ꥹ�Ȥ�ե�����˵�Ͽ����
     */
    function _save_summary() {
        $filepath = CACHE_DIR . PLUGIN_GENERATE_TAGS_SUMMARY_STORAGE_FILENAME;
        $handle = fopen($filepath, "w");
        fwrite($handle, serialize(array(
            "tags_to_pages" => $this->_tags_to_pages,
            "taggroups_to_tags" => $this->_taggroups_to_tags,
        )));
        fclose($handle);
    }

    /**
     * �ƥڡ��������ꤵ��Ƥ��륿����������ƥ����ѿ��س�Ǽ����
     *
     * @see $_tags_to_pages
     */
    function _set_tags_to_pages(){

        $pages = get_existpages();
        $page_to_tags = array();
        foreach ($pages as $k => $v) {
            $tags = $this->pick($v);
            if (count($tags) > 0) $page_to_tags[$v] = $tags;
        }

        // ������졼�����ǽ����Ѥ��뤫�ݤ��Υե饰
        $use_tagrelation = !!count(get_source(PLUGIN_GENERATE_TAGS_TAGRELATION_CONFIG_PAGENAME));
        if ($use_tagrelation) $this->_set_tagrelations();

        foreach ($page_to_tags as  $page => $tags) {

            // ������졼�����ǽ�ˤ��ڡ�����Υ����ν���
            if ($use_tagrelation) $tags = $this->append_tag_by_tagrelation($tags);

            foreach ($tags as $tag) {
                if (array_key_exists($tag, $this->_tags_to_pages)) {
                    $this->_tags_to_pages[$tag][] = $page;
                } else {
                    $this->_tags_to_pages[$tag] = array($page);
                }
            }
        }
    }

    /**
     * �������롼������ڡ����ξ����������ƥ����ѿ��س�Ǽ����
     *
     * @see $_taggroups_to_tags
     * @desc PukiWiki��ˡ�ˤ��ơ��֥��ȤߤιԤ����Ȥ���ǧ�����롣
     *       �㤨�аʲ��Τ褦��PukiWiki�������Ԥ�ǧ�����롣
     *       |Linux|debian,ubntsu,redhat,centos,freebsd|
     *       |RDB|mysql,postgresql,oraclesql,sqlite|
     *       ����ʳ��ιԤ�̵�뤹�롣
     * @desc ñ�������ˤ���Ⱦ�Ѷ���ϥȥ�ह�롣
     *       | a | aa, ab , bc|
     *       ����� "a" "aa" "ab" "bc" �Ȥ���ǧ������롣
     */
    function _set_taggroups_to_tags(){
        foreach (get_source(PLUGIN_GENERATE_TAGS_TAGGROUP_CONFIG_PAGENAME) as $row) {
            if (preg_match('/^\\| *([^|]+?) *\\| *([^|]+?) *\\|/', $row, $matches))
                $this->_taggroups_to_tags[$matches[1]] = split(' *, *', $matches[2]);
        }
    }

    /**
     * ������졼���������ڡ����ξ����������ƥ����ѿ��س�Ǽ����
     *
     * @see _get_tagrelations
     */
    function _set_tagrelations(){
        $this->_tagrelations = $this->get_tagrelations();
    }

    /**
     * �ƥ����ѥڡ�����������
     */
    function _delete_tag_pages(){
        // ��������
        if ($this->_with_truncate) {
            foreach (get_existpages() as $page) {
                $p = '#^' . preg_quote(PLUGIN_GENERATE_TAGS_TAG_PAGE_PREFIX, '#') . '#';
                if (preg_match($p, $page) === 1) page_write($page, "");
            }
        // ��ʬ����
        } else {
            // ����¸�ߤ����ڡ����Ǻ���¸�ߤ��ʤ��ڡ����Τߤ�������
            foreach ($this->_pre_tags_to_pages as $tag => $devnull) {
                if (array_key_exists($tag, $this->_tags_to_pages) === false)
                    page_write(PLUGIN_GENERATE_TAGS_TAG_PAGE_PREFIX . $tag, "");
            }
            foreach ($this->_pre_taggroups_to_tags as $taggroup => $devnull) {
                if (array_key_exists($taggroup, $this->_taggroups_to_tags) === false)
                    page_write(PLUGIN_GENERATE_TAGS_TAG_PAGE_PREFIX
                                . PLUGIN_GENERATE_TAGS_TAGGROUP_PAGE_PREFIX . $taggroup, "");
            }
        }
    }

    /**
     * �ƥ����ѥڡ�������������
     */
    function _generate_tag_pages(){
        foreach ($this->_tags_to_pages as $tag => $pages) {

            // ��ʬ�����ξ����ѹ������ä��ڡ��������򹹿�����
            if ($this->_with_truncate === false) {
                if (count(array_diff((array)$this->_pre_tags_to_pages[$tag], $pages)) === 0
                    && count(array_diff($pages, (array)$this->_pre_tags_to_pages[$tag])) === 0) continue;
            }

            sort($pages);
            $tagpagename = PLUGIN_GENERATE_TAGS_TAG_PAGE_PREFIX . $tag;
            $src = "#back(back, left, 0)\n";
            $src .= "* " . $tag . "\n";
            foreach ($pages as $page) $src .= "-[[" . $page . "]]\n";
            page_write($tagpagename, $src);
        }
    }

    /**
     * �ƥ������롼���ѥڡ�������������
     */
    function _generate_taggroup_pages(){

        foreach ($this->_taggroups_to_tags as $taggroup => $tags) {

            $pages = $this->_get_taggroup_unique_pages($taggroup);
            $pre_pages = $this->_get_taggroup_unique_pages($taggroup, true);

            // ��ʬ�����ξ����ѹ������ä��ڡ��������򹹿�����
            //   ��°���륿�����롼�פ��ѹ������뤫��������ɳ�դ��ڡ������ѹ������뤫�򸫤Ƥ���
            if ($this->_with_truncate === false) {
                if (   count(array_diff((array)$this->_taggroups_to_tags[$taggroup],
                                        (array)$this->_pre_taggroups_to_tags[$taggroup])) === 0
                    && count(array_diff((array)$this->_pre_taggroups_to_tags[$taggroup],
                                        (array)$this->_taggroups_to_tags[$taggroup])) === 0
                    && count(array_diff($pages, $pre_pages)) === 0
                    && count(array_diff($pre_pages, $pages)) === 0
                ) continue;
            }

            $src = "#back(back, left, 0)\n";
            $src .= "* " . $taggroup . "\n";
            $src .= "These pages were tagged with ";
            $c = 0;
            foreach ($tags as $tag) {
                if ($c > 0) $src .= " or ";
                $src .= "\"[[$tag>" . PLUGIN_GENERATE_TAGS_TAG_PAGE_PREFIX . "$tag]]\"";
                $c++;
            }
            $src .= ".\n";
            foreach ($pages as $page) $src .= "-[[$page]]\n";

            $page_name = PLUGIN_GENERATE_TAGS_TAG_PAGE_PREFIX;
            $page_name .= PLUGIN_GENERATE_TAGS_TAGGROUP_PAGE_PREFIX . $taggroup;
            page_write($page_name, $src);
        }
    }

    /**
     * ���꥿�����롼�פΥڡ���̾�ꥹ�Ȥ��������
     *
     * @desc Ʊ̾�ڡ����ϥ�ˡ���������Ƥ���
     * @param str $taggroup �������롼��̾
     * @param bool $pre     false=����Υǡ�����true=����Υǡ���
     *                      $_tags_to_pages��$_pre_taggroups_to_tags����
     * @return arr "base64�������ڡ���̾" => "�ڡ���̾" �Υ��åȤΥꥹ��
     */
    function _get_taggroup_unique_pages($taggroup, $pre = false){
        $tags = ($pre)? (array)$this->_pre_taggroups_to_tags[$taggroup]: (array)$this->_taggroups_to_tags[$taggroup];
        sort($tags);
        $pages = array();
        foreach ($tags as $tag) {
            $tagpages = ($pre)? (array)$this->_pre_tags_to_pages[$tag]: (array)$this->_tags_to_pages[$tag];
            foreach ($tagpages as $page) {
                $pages[base64_encode($page)] = $page;
            }
        }
        sort($pages);
        return $pages;
    }
}
?>
