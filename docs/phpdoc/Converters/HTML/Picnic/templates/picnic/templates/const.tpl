{section name=consts loop=$consts}
{if $show == 'summary'}
  var {$consts[consts].const_name}, {$consts[consts].sdesc}
{else}
  <a name="{$consts[consts].const_dest}"></a>
  <h4 class="const-header">{$consts[consts].const_name} <span class="value">= {$consts[consts].const_value|replace:"\n":"<br>\n"|replace:" ":"&nbsp;"|replace:"\t":"&nbsp;&nbsp;&nbsp;"}</span></h4>
  <div class="smalllinenumber">
    [line {if $consts[consts].slink}{$consts[consts].slink}{else}{$consts[consts].line_number}{/if}]&nbsp;&nbsp;
    [<a href="#top">top</a>]
  </div>
  {include file="docblock.tpl" sdesc=$consts[consts].sdesc desc=$consts[consts].desc tags=$consts[consts].tags}
{/if}
{/section}
