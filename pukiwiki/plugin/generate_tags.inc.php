<?php
/**
 * 各ページが持つタグをまとめてファイルに保存する
 *
 * @desc ":config/plugin/generate_tags/taggroups"というページ名のPukiWikiページが作成されている場合、
 *       タグのグルーピング機能が有効になる。
 *       ページ内には以下のように、PukiWiki記法のテーブル組みを使って設定する。
 *       |タググループ名1|タグ名1,タグ名2,タグ名3|
 *       |タググループ名2|タグ名3,タグ名4,タグ名5|
 * @desc ":config/plugin/generate_tags/tagrelations"というページ名のPukiWikiページが作成されている場合、
 *       タグのリレーション機能が有効になる。
 *       ページ内には以下のように、PukiWiki記法のテーブル組みを使って設定する。
 *       |centos,ubuntu,freebsd|linux       |
 *       |actionscript,flex    |flash       |
 *       |flex                 |actionscript|
 *       ※centos か ubuntu か freebsd タグをつけた場合は linux タグが自動的に振られる
 *       ※再帰的な判断はしない、例えば
 *         |a|b|
 *         |b|c|
 *         このときには "aがあるならbが振られる" "bがあるならcが振られる" が
 *         "aがあるならcが振られる"にはならない
 * @desc 第1引数で表示に関するオプションを指定できる
 *       "&"区切りで項目を分割する。解析の詳細は plugin_generate_tags_parse_option_argを参照
 *       指定できるオプションは以下の通り
 *       "name=名前"   -> タグ生成ボタンを"名前"のラベルに変更する
 *       "tagrelation" -> タグリレーション設定ページへのリンクをボタンの横に出力する
 * @desc 第2引数で生成処理に関するオプションを指定できる
 *       "&"区切りで項目を分割する。解析の詳細は plugin_generate_tags_parse_option_argを参照
 *       指定できるオプションは以下の通り
 *       "with-truncate"
 *         -> タグ・タググループページの生成は、変更のあったページのみを差分更新で行っているが
 *            このオプションをつけることで変更の無いページも含めて全てを再生成するフローになる
 *            タグ・タググループ情報がなんらかの理由で破壊されたときに、一度だけ指定して実行する
 * @release_version 1.7
 * @author kjirou <kjirou.web[at-mark]gmail.com>
 *                <http://kjirou.sakura.ne.jp/mt/>
 * @license MIT License http://www.opensource.org/licenses/mit-license.php
 */
define("PLUGIN_GENERATE_TAGS_TAGGING_PLUGIN_NAME", "set_tags");
define("PLUGIN_GENERATE_TAGS_TAG_PAGE_PREFIX", "tag/");
define("PLUGIN_GENERATE_TAGS_TAGGROUP_PAGE_PREFIX", "taggroup/");
/** 各種情報を保存するファイル名、cacheディレクトリに保存する */
define("PLUGIN_GENERATE_TAGS_SUMMARY_STORAGE_FILENAME", "plugin_generate_tags_summary_storage.txt");
/** タググループ機能を有効にする場合に作成する設定ファイルのPukiWikiページ名 */
define("PLUGIN_GENERATE_TAGS_TAGGROUP_CONFIG_PAGENAME", ":config/plugin/generate_tags/taggroups");
/** タグリレーション機能を有効にする場合に作成する設定ファイルのPukiWikiページ名 */
define("PLUGIN_GENERATE_TAGS_TAGRELATION_CONFIG_PAGENAME", ":config/plugin/generate_tags/tagrelations");

function plugin_generate_tags_action()
{
    global $post, $vars;

    #// 凍結・認証のチェック
    #check_editable($post['page'], true, true);

    // タグ情報の集約・タグ・タググループ生成
    $tm =& new PluginGenerateTags_TagManager;
    if (array_key_exists("with-truncate", $post) && !!$post["with-truncate"]) {
        $tm->set_with_truncate(true);
    }
    $tm->generate();

    // リダイレクト
    $vars["refer"] = $vars["page"];
    return array("msg" => "", "body" => "");
}

function plugin_generate_tags_convert()
{
    global $vars;

    $args = func_get_args();
    $vopts = plugin_generate_tags_parse_option_arg($args[0]);
    $gopts = plugin_generate_tags_parse_option_arg($args[1]);

    // サブミットボタン名の変更
    $btn_name = "Generate tags";
    if (array_key_exists("name", $vopts) && strlen($vopts["name"]) > 0) {
        $btn_name = $vopts["name"];
    }

    $html = '';
    $html .= '<form action="' . get_script_uri() . '" method="post" style="margin:0;">';
    $html .= '<input type="hidden" name="page" value="' . htmlspecialchars($vars['page'], ENT_QUOTES) . '" />';
    // "with-truncate"オプション
    if (array_key_exists("with-truncate", $gopts)) {
        $html .= '<input type="hidden" name="with-truncate" value="1" />';
    }
    $html .= '<input type="hidden" name="plugin" value="generate_tags" />';
    $html .= '<input type="submit" value="' . $btn_name . '" />';
    // タグリレーション設定ファイルへのリンクを出すか
    if (array_key_exists("tagrelation", $vopts)) {
        $encoded = rawurlencode(PLUGIN_GENERATE_TAGS_TAGRELATION_CONFIG_PAGENAME);
        $html .= ' <a href="' . get_script_uri() . '?' . $encoded . '" style="font-size:9px;">edit tagrelation</a>';
    }
    $html .= '</form>';

    return $html;
}

/**
 * オプション形式の引数文字列を解析する
 *
 * @example
 *     "a=1&bb=22&ccc=333" -> array("a" => "1", "bb" => "22", "ccc" => "333")
 *     "name=タグ生成&tagrelation" -> array("name" => "タグ生成", "tagrelation" => "")
 * @param str $arg オプション形式の引数、現在はPukiWikiプラグイン第1・2引数が相当する
 * @return array オプションリスト
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
 * タグ管理クラス
 */
class PluginGenerateTags_TagManager {

    /**
     * タグ情報リスト
     *
     * @example
     *     array(
     *         "タグ名1" => array("ページ名1", "ページ名2", "ページ名3"),
     *         "タグ名2" => array("ページ名1", "ページ名3"),
     *         "タグ名3" => array("ページ名2", "ページ名3"),
     *     );
     * @var array
     */
    var $_tags_to_pages = array();
    var $_pre_tags_to_pages = array(); // 前回のデータ

    /**
     * タググループ情報リスト
     *
     * @example
     *     array(
     *         "タググループ名1" => array("タグ名1", "タグ名2", "タグ名3"),
     *         "タググループ名2" => array("タグ名1", "タグ名3"),
     *         "タググループ名3" => array("タグ名2", "タグ名3"),
     *     );
     * @var array
     */
    var $_taggroups_to_tags = array();
    var $_pre_taggroups_to_tags = array(); // 前回のデータ

    /**
     * タグリレーション情報リスト
     *
     * @example
     *     array(
     *         "タグ名A" => array("タグ名1", "タグ名2", "タグ名3"),
     *         "タグ名B" => array("タグ名1", "タグ名3"),
     *     );
     *     ※タグ名1 か タグ名2 か タグ名3 をつけた場合は、タグ名Aが自動的に振られる
     *     ※タグ名1 か タグ名3 をつけた場合は、タグ名Bが自動的に振られる
     * @var array
     */
    var $_tagrelations = array();

    /** タグ・タググループ生成時に差分更新ではなく全部更新で行うか */
    var $_with_truncate = false;


    function PluginGenerateTags_TagManager(){
    }

    /**
     * タグに関わる各種情報を生成する
     */
    function generate() {

        // 全部更新の場合はストレージファイルを削除する
        $strage_filepath = CACHE_DIR . PLUGIN_GENERATE_TAGS_SUMMARY_STORAGE_FILENAME;
        if ($this->_with_truncate && file_exists($strage_filepath)) unlink($strage_filepath);

        // 前回保存したデータを過去データとして保持
        $summary = $this->get_summary();
        $this->_pre_tags_to_pages = $summary["tags_to_pages"];
        $this->_pre_taggroups_to_tags = $summary["taggroups_to_tags"];

        // タググループ機能を使用するか否かのフラグ
        $use_taggroup = !!count(get_source(PLUGIN_GENERATE_TAGS_TAGGROUP_CONFIG_PAGENAME));

        $this->_set_tags_to_pages();
        if ($use_taggroup) $this->_set_taggroups_to_tags();
        $this->_save_summary();
        $this->_delete_tag_pages();
        $this->_generate_tag_pages();
        if ($use_taggroup) $this->_generate_taggroup_pages();
    }

    /**
     * 各種情報リストをストレージから取得する
     *
     * @return array
     *           "tags_to_pages"        : "タグ名" => array("ページ名1", "ページ名2")
     *                                    のリストが含まれている配列
     *           "taggroups_to_tags"    : "タググループ名" => array("タグ名1", "タグ名2")
     *                                    のリストが含まれている配列
     *           *ファイルが無い場合は、それぞれの中身が空配列になる
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
     * PukiWikiソース内に設定されているタグのリストを取得する
     *
     * @desc
     *     PukiWikiソース内に記述されている以下のようなプラグインを解析し
     *     #set_tags(あとで読む,php,javascript) は
     *     array("あとで読む", "php", "javascript") を返す。
     * @desc
     *     タグはカンマ区切りで分割され、タグ前後の半角空白はトリムされる
     *     #set_tags(   aaa,   bbb,     c cc   ) -> array("aaa", "bbb", "c cc")
     *     連続したカンマもトリムされる
     *     #set_tags(aaa, ,   ,  , bbb, ccc) -> array("aaa", "bbb", "ccc")
     *     半角大文字英字は小文字に変換される
     *     #set_tags(AAA) -> array("aaa")
     * @param str $page PukiWikiページ名
     * @return array タグのリスト
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
                // "#set_tags()", "#set_tags(  )" 等の中身が空の指定があった場合
                // ここではじかないと "tag/" のページが出来てしまう
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
     * タグリレーション設定ページの情報を取得する
     *
     * @see $_tagrelations
     * @desc PukiWikiソースの解析については_set_taggroups_to_tagsに準ずる
     * @desc taggroupではsetとgetと分けていないのにこっちで分けているのは
     *       set_tags.inc.phpでこの部分だけ使うため
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
     * タグリレーション情報によりタグを追加する
     *
     * @param arr $tags         1ページに設定されているタグリスト
     * @param arr $tagrelations get_tagrelationsで取得できるものと等価
     *                          メンバ変数を使えない=静的コール用に設けた引数
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
     * タグ情報リストをファイルに記録する
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
     * 各ページに設定されているタグを取得してメンバ変数へ格納する
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

        // タグリレーション機能を使用するか否かのフラグ
        $use_tagrelation = !!count(get_source(PLUGIN_GENERATE_TAGS_TAGRELATION_CONFIG_PAGENAME));
        if ($use_tagrelation) $this->_set_tagrelations();

        foreach ($page_to_tags as  $page => $tags) {

            // タグリレーション機能によるページ毎のタグの修正
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
     * タググループ設定ページの情報を取得してメンバ変数へ格納する
     *
     * @see $_taggroups_to_tags
     * @desc PukiWiki記法によるテーブル組みの行を情報として認識する。
     *       例えば以下のようなPukiWikiソース行を認識する。
     *       |Linux|debian,ubntsu,redhat,centos,freebsd|
     *       |RDB|mysql,postgresql,oraclesql,sqlite|
     *       それ以外の行は無視する。
     * @desc 単語の前後にある半角空白はトリムする。
     *       | a | aa, ab , bc|
     *       これは "a" "aa" "ab" "bc" として認識される。
     */
    function _set_taggroups_to_tags(){
        foreach (get_source(PLUGIN_GENERATE_TAGS_TAGGROUP_CONFIG_PAGENAME) as $row) {
            if (preg_match('/^\\| *([^|]+?) *\\| *([^|]+?) *\\|/', $row, $matches))
                $this->_taggroups_to_tags[$matches[1]] = split(' *, *', $matches[2]);
        }
    }

    /**
     * タグリレーション設定ページの情報を取得してメンバ変数へ格納する
     *
     * @see _get_tagrelations
     */
    function _set_tagrelations(){
        $this->_tagrelations = $this->get_tagrelations();
    }

    /**
     * 各タグ用ページを削除する
     */
    function _delete_tag_pages(){
        // 全部更新
        if ($this->_with_truncate) {
            foreach (get_existpages() as $page) {
                $p = '#^' . preg_quote(PLUGIN_GENERATE_TAGS_TAG_PAGE_PREFIX, '#') . '#';
                if (preg_match($p, $page) === 1) page_write($page, "");
            }
        // 差分更新
        } else {
            // 前回存在したページで今回存在しないページのみを削除する
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
     * 各タグ用ページを生成する
     */
    function _generate_tag_pages(){
        foreach ($this->_tags_to_pages as $tag => $pages) {

            // 差分更新の場合は変更があったページだけを更新する
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
     * 各タググループ用ページを生成する
     */
    function _generate_taggroup_pages(){

        foreach ($this->_taggroups_to_tags as $taggroup => $tags) {

            $pages = $this->_get_taggroup_unique_pages($taggroup);
            $pre_pages = $this->_get_taggroup_unique_pages($taggroup, true);

            // 差分更新の場合は変更があったページだけを更新する
            //   所属するタググループに変更があるか、タグに紐付くページに変更があるかを見ている
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
     * 指定タググループのページ名リストを取得する
     *
     * @desc 同名ページはユニーク化されている
     * @param str $taggroup タググループ名
     * @param bool $pre     false=今回のデータ、true=前回のデータ
     *                      $_tags_to_pagesと$_pre_taggroups_to_tags参照
     * @return arr "base64化したページ名" => "ページ名" のセットのリスト
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
