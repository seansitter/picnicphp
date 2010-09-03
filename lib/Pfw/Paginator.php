<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * @package       Pfw
 * @author        Sean Sitter <sean@picnicphp.com>
 * @copyright     2010 The Picnic PHP Framework
 * @license       http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 * @link          http://www.picnicphp.com
 * @since         0.10
 * @filesource
 */

/**
 * Short description for file
 *
 * Long description for file (if any)...
 * 
 * @category      Framework
 * @package       Pfw
 */
class Pfw_Paginator
{
    const DEFAULT_PER_PAGE = 10;
    
    protected $offset;
    protected $collection;
    protected $per_page;
    protected $page_num;
    protected $max_pages;
    protected $total;
    protected $qs_str;

    function __construct($per_page, $page_num, $qs_str){
        $this->per_page = $per_page;
        $this->page_num = $page_num;
        $this->qs_str = $qs_str;
        $this->total = -1;
    }

    function getPaginator($qs_var_name = 'pg'){
        $page_num = $_GET[$qs_var_name];
        $page_num = (!empty($page_num)) ? intval($page_num) : 1;

        # do all the query string stuff
        $qs = $_GET;
        unset($qs[$qs_var_name]);

        $qs_str = "";
        foreach($qs as $key => $value){
            if($key == $qs_var_name){
                continue;
            }
            $value = urlencode($value);
            $qs_str .= "&{$key}={$value}";
        }
        $amp = (empty($qs_str)) ? "" : "&";
        $qs_str = "?" . $qs_str . "{$amp}{$qs_var_name}=";

        return new Pfw_Paginator(self::DEFAULT_PER_PAGE, $page_num, $qs_str);
    }

    function getOffset(){
        $this->offset = ($this->getPageNum() - 1) * $this->getPerPage();
        return $this->offset;
    }

    function setPerPage($per_page){
        $this->per_page = intval($per_page);
    }

    function getPerPage(){
        return $this->per_page;
    }

    function getPageNum(){
        if($this->page_num < 0){
            $this->page_num = ceil($this->getTotal() / $this->getPerPage());
        }
        return $this->page_num;
    }

    function getPaginationHTML(){
        $curr_pgnum = $this->getPageNum();
        $total = $this->getTotal();
        $per_page = $this->getPerPage();
        $link = $this->getQS();

        if ($total < $per_page) { return ""; }

        if (!$curr_pgnum) { $curr_pgnum = 1; }
        $fromr = (($curr_pgnum-1) * $per_page) + 1;
        $tor = $fromr+$per_page-1;
        $tor = ($tor >= $total) ? $total : $tor;

        $pgnum = $i + 1;

        $rv .= '<ul class="pfw-pager">';

        # Prev
        $rv .= '';
        if ($curr_pgnum > 1) {
            $prev = $curr_pgnum - 1;
            $rv .= "<span class=firstpage><a href='{$link}{$firstpage}'>&#171;</a></span>";
            $rv .= "<span class=prev><a href='{$link}{$prev}'>&lt; Prev</a></span>";
        } else {
            $rv .= "<span class=notactive>&#171; </span>";
            $rv .= "<span class=notactive style='font-size:0.85em;'>&lt; Prev</span>";
        }
        $rv .= " <span class=notactive>|</span><span class=results>";

        $result_currentpage =$curr_pgnum;

        if( $lastpage>$page_limit ){
            if( ($lastpage-$result_currentpage) <= 2){
                $result_firstpage = $lastpage-($page_limit-1);
                $result_lastpage = $lastpage;
            }
            else{
                $result_firstpage = $result_currentpage-1;
                $result_lastpage = $result_currentpage+2;
            }
        }

        if($lastpage<$page_limit || $result_currentpage == $firstpage){
            $result_firstpage = $firstpage;
        }

        if($lastpage<=$page_limit){
            $result_lastpage = $lastpage;
        }else{
            $result_lastpage = $result_firstpage+3;
        }

        for($page_count=$result_firstpage; $page_count<=$result_lastpage; $page_count++){
            if($page_count == $curr_pgnum){
                $rv .= "&nbsp;<span class=currentpage>$page_count</span>&nbsp;";
            }else{
                $rv .= "&nbsp;<a href='{$link}{$page_count}'>$page_count</a>&nbsp;";
            }
        }
        $rv .= "</span><span class=notactive>|</span> ";

        # Next
        if (($curr_pgnum * $per_page) < $total) {
            $next = ($nextpage) ? $nextpage : $curr_pgnum + 1;
            $rv .= "<span class=next><a href='{$link}{$next}'>Next &gt;</a></span>";
            $rv .= "<span class=lastpage><a href='{$link}{$lastpage}'>&#187;</a></span>";
        } else {
            $rv .= "<span class=notactive style='font-size:0.85em;'> Next &gt;</span>";
            $rv .= "<span class=notactive> &#187;</span>";
        }
        $rv .= "</div>";

        return $rv;
    }

    function setCollection($collection){
        $this->collection = $collection;
    }

    function getCollection(){
        return $this->collection;
    }

    function setTotal($total){
        $this->total = intval($total);
    }

    function getTotal(){
        return $this->total;
    }

    function getQS(){
        return $this->qs_str;
    }

    function getFullQs(){
        return $this->getQS() . $this->getPageNum();
    }

    function setMaxPages($max){
        $this->max_pages = intval($max);
    }
}
