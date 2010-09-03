{include file="header.tpl" eltype="class" hasel=true contents=$classcontents}

<div id="wrapper">
{include file="side.tpl"}

<div id="main-wrapper">
<div id="main">
{php}
$methods = $this->get_template_vars('methods');
$public_methods = array();
$protected_methods = array();
foreach ($methods as $method) {
  foreach($method['api_tags'] as $tag) {
    if ($tag['keyword'] == 'access') {
      if ($tag['data'] == 'public') {
        array_push($public_methods, $method);
      }
      if ($tag['data'] == 'protected') {
        array_push($protected_methods, $method);
      }
    }
  }
}
$this->assign('public_methods', $public_methods);
$this->assign('protected_methods', $protected_methods);


$vars = $this->get_template_vars('vars');
$public_vars = array();
$protected_vars = array();
foreach ($vars as $var) {
  foreach($var['api_tags'] as $tag) {
    if ($tag['keyword'] == 'access') {
      if ($tag['data'] == 'public') {
        array_push($public_vars, $var);
      }
      if ($tag['data'] == 'protected') {
        array_push($protected_vars, $var);
      }
    }
  }
}
$this->assign('public_vars', $public_vars);
$this->assign('protected_vars', $protected_vars);

//print "<pre>".print_r($public_vars, true)."</pre>";
{/php}


<h1>Class: {$class_name}</h1>
{$source_location}

<h3><a href="#class-details">{if $is_interface}Interface{else}Class{/if} Overview</a></h3>
<div class="description">
{if $sdesc}
<p>{$sdesc|default:''}</p>
{/if}
{if $desc}
<p>{$desc}</p>
{/if}
</div>

{if $implements}
<h3><a name="implements-interface">Implements Interfaces</a></h3>
<ul class="item-list">
  {foreach item="int" from=$implements name=interfaces}
  <li>{$int}{if !$smarty.foreach.interfaces.last},{/if}</li>
  {/foreach}
</ul>
{/if}

<h3>
  <a href="#class-methods">Methods</a>
  <span class="access-specific-links">
    {if $public_methods & count($public_methods) > 0}
    <a href="#public_methods">[public]</a>&nbsp;
    {/if}
    {if $protected_methods & count($protected_methods) > 0}
    <a href="#protected_methods">[protected]</a>
    {/if}
  </span>
</h3>
<ul class="item-list">
  {section name=contents loop=$contents.method}
  <li>{$contents.method[contents]}{if !$smarty.section.contents.last},{/if}</li>
  {sectionelse}
  No accessible methods defined, see <a href="#inherited-methods">inherited methods</a>.
  {/section}
</ul>

{if $tutorial}
<h4 class="classtutorial">{if $is_interface}Interface{else}Class{/if} Tutorial:</h4>
<ul>
	<li>{$tutorial}</li>
</ul>
{/if}

{*
{if count($tags) > 0}
<h4>Author(s):</h4>
<ul>
  {section name=tag loop=$tags}
    {if $tags[tag].keyword eq "author"}
    <li>{$tags[tag].data}</li>
    {/if}
  {/section}
</ul>
{/if}
*}

{assign var="version" value=""}
{assign var="copyright" value=""}

{*
{section name=tag loop=$tags}
  {if $tags[tag].keyword eq "version"}
  {assign var="version" value=$tags[tag].data}
  {/if}
  {if $tags[tag].keyword eq "copyright"}
  {assign var="copyright" value=$tags[tag].data}
  {/if}
{/section}
*}

{if $version}
<h4>Version:</h4>
<ul>
  <li>{$version}</li>
</ul>
{/if}

{if $copyright}
<h4>Copyright:</h4>
<ul>
  <li>{$copyright}</li>
</ul>
{/if}


</td>

{if count($contents.var) > 0}
<h3>
  <a href="#class_vars">Variables</a>
  <span class="access-specific-links">
    {if $public_vars & count($public_vars) > 0}
    <a href="#public_vars">[public]</a>&nbsp;
    {/if}
    {if $protected_vars & count($protected_vars) > 0}
    <a href="#protected_vars">[protected]</a>
    {/if}
  </span>
</h3>
<ul class="item-list">
  {section name=contents loop=$contents.var}
  <li>{$contents.var[contents]}{if !$smarty.section.contents.last},{/if}</li>
  {/section}
</ul>
{/if}

{if count($contents.const) > 0}
<h3><a href="#class_consts">Constants</a></h3>
<ul class="item-list">
  {section name=contents loop=$contents.const}
  <li>{$contents.const[contents]}{if !$smarty.section.contents.last},{/if}</li>
  {/section}
</ul>
{/if}

{if $children}
<h3><a name="child-classes">Child classes</a></h3>
<div class="tags">
{section name=kids loop=$children}
<dl style="margin-bottom: 16px;">
  <dt {if $children[kids].link}style="margin-bottom: 0px;"{/if}>{$children[kids].link}</dt>
  <dd {if $children[kids].sdesc}style="margin-bottom: 8px;"{/if}>{$children[kids].sdesc}</dd>
</dl>
{/section}
</div>
{/if}

{if $class_tree.classes > 0}
<h3><a name="inheritence-tree">Inheritance Tree</a></h3>
<pre class="inheritence-tree">
  {section name=tree loop=$class_tree.classes}{$class_tree.classes[tree]}{$class_tree.distance[tree]}{/section}
</pre>
{/if}

{if $imethods && count($imethods) > 0}
<h3><a name="inherited-methods">Inherited Methods</a></h3>
<div class="tags">
{section name=imethods loop=$imethods}
<h4>From Class: {$imethods[imethods].parent_class}</h4>
<dl style="margin-bottom: 16px;">
  {section name=im2 loop=$imethods[imethods].imethods}
  <dt {if !$imethods[imethods].imethods[im2].sdesc}style="margin-bottom: 0px;"{/if}>
    {$imethods[imethods].imethods[im2].link}
  </dt>
  {if $imethods[imethods].imethods[im2].sdesc}
  <dd {if $imethods[imethods].imethods[im2].sdesc}style="margin-bottom: 8px;"{/if}>
    {$imethods[imethods].imethods[im2].sdesc}
  </dd>
  {/if}
  {/section}
</dl>
{/section}
</div>
{/if}

{if $iconsts && count($iconsts) > 0}
<h3>Inherited Constants</h3>
{section name=iconsts loop=$iconsts}
<div class="tags">
<h4>From Class: {$iconsts[iconsts].parent_class}</h4>
<dl>
{section name=iconsts2 loop=$iconsts[iconsts].iconsts}
<dt>
  {$iconsts[iconsts].iconsts[iconsts2].link}
</dt>
<dd>
  {$iconsts[iconsts].iconsts[iconsts2].iconsts_sdesc} 
</dd>
{/section}
</dl>
</div>
{/section}
{/if}

{if $ivars && count($ivars) > 0}
<td valign="top">
<h3>Inherited Variables</h3>
{section name=ivars loop=$ivars}
<div class="tags">
<h4>From Class: {$ivars[ivars].parent_class}</h4>
<dl>
{section name=ivars2 loop=$ivars[ivars].ivars}
<dt>
  {$ivars[ivars].ivars[ivars2].link}
  {* original <a href="{$ivars[ivars].ivars[ivars2].ipath #{$ivars[ivars].ivars[ivars2].ivar_name ">{$ivars[ivars].ivars[ivars2].ivar_name </a> *}
</dt>
<dd>
  {$ivars[ivars].ivars[ivars2].ivars_sdesc} 
</dd>
{/section}
</dl>
</div>
{/section}
</td>
{/if}

<hr style="margin: 25px 0px; height: 0px; border: 0px; border-bottom: 2px dashed #555;"/>

{*
<a name="class_details"></a>
<h3>Class Details</h3>
<div class="top">[ <a href="#top">Top</a> ]</div>
*}

{if $public_methods & count($public_methods) > 0}
<a name="public_methods"></a>
<h3 style="margin-bottom: -15px;">
Public Class Methods
</h3>
<div class="tags">
{include file="method.tpl" methods=$public_methods}
</div>
{/if}

{if $protected_methods & count($protected_methods) > 0}
<a name="protected_methods"></a>
<h3 style="margin-bottom: -15px;">
Protected Class Methods
</h3>
<div class="tags">
{include file="method.tpl" methods=$protected_methods}
</div>
{/if}

{if $public_vars && count($public_vars) > 0}
<a name="public_vars"></a>
<h3 style="margin-bottom: -15px;">
Public Class Variables
</h3>
<div class="tags">
{include file="var.tpl" vars=$public_vars}
</div>
{/if}

{if $protected_vars && count($protected_vars) > 0}
<a name="protected_vars"></a>
<h3 style="margin-bottom: -15px;">
Protected Class Variables
</h3>
<div class="tags">
{include file="var.tpl" vars=$protected_vars}
</div>
{/if}

{if $consts && count($consts) > 0}
<h3 style="margin-bottom: 5px;">
  <a name="class_consts">Class Constants</a>
</h3>
<div class="tags">
{include file="const.tpl"}
</div>
{/if}

</div> {*end main*}
</div> {*end main-wrapper*}
</div> {*end wrapper*}

{include file="footer.tpl"}