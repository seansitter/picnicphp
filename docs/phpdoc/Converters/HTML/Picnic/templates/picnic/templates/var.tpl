{section name=vars loop=$vars}
{if $vars[vars].static}
{if $show == 'summary'}
  static var {$vars[vars].var_name}, {$vars[vars].sdesc}<br>
{else}
  <a name="{$vars[vars].var_dest}"></a>
  <h4 class="var-header">
    static {$vars[vars].var_name} <span class="value">= {$vars[vars].var_default|replace:"\n":"<br>\n"|replace:" ":"&nbsp;"|replace:"\t":"&nbsp;&nbsp;&nbsp;"}</span>
  </h4>
  <div class="smalllinenumber">
    [line {if $vars[vars].slink}{$vars[vars].slink}{else}{$vars[vars].line_number}{/if}]&nbsp;&nbsp;[<a href="#top">top</a>]
  </div>
  {include file="docblock.tpl" sdesc=$vars[vars].sdesc desc=$vars[vars].desc tags=$vars[vars].tags}

  <div class="tags">
  <table border="0" cellspacing="0" cellpadding="0">
    <tr>
      <td><b>Type:</b>&nbsp;&nbsp;</td>
      <td>{$vars[vars].var_type}</td>
    </tr>
    {if $vars[vars].var_overrides != ""}
    <tr>
      <td><b>Overrides:</b>&nbsp;&nbsp;</td>
      <td>{$vars[vars].var_overrides}</td>
    </tr>
    {/if}
  </table>
  </div>
{/if}
{/if}
{/section}

{section name=vars loop=$vars}
{if !$vars[vars].static}
{if $show == 'summary'}
  var {$vars[vars].var_name}, {$vars[vars].sdesc}<br>
{else}
  <a name="{$vars[vars].var_dest}"></a>
  <h4 class="var-header">{$vars[vars].var_name} <span class="value">= {$vars[vars].var_default|replace:"\n":"<br>\n"|replace:" ":"&nbsp;"|replace:"\t":"&nbsp;&nbsp;&nbsp;"}</span></h4>
  <div class="smalllinenumber">
    [line {if $vars[vars].slink}{$vars[vars].slink}{else}{$vars[vars].line_number}{/if}]&nbsp;&nbsp;[<a href="#top">top</a>]
  </div>
  {include file="docblock.tpl" sdesc=$vars[vars].sdesc desc=$vars[vars].desc tags=$vars[vars].tags}

  <div class="tags">
  <table border="0" cellspacing="0" cellpadding="0">
    <tr>
      <td><b>Type:</b>&nbsp;&nbsp;</td>
      <td>{$vars[vars].var_type}</td>
    </tr>
    {if $vars[vars].var_overrides != ""}
    <tr>
      <td><b>Overrides:</b>&nbsp;&nbsp;</td>
      <td>{$vars[vars].var_overrides}</td>
    </tr>
    {/if}
  </table>
  </div>
{/if}
{/if}
{/section}
