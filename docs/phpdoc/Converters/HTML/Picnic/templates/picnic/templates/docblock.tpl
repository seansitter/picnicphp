{*
{if $sdesc != ''}{$sdesc|default:''}<br /><br />{/if}
{if $desc != ''}{$desc|default:''}<br />{/if}
*}
{if count($tags) > 0}
{if $showtagheader !== false}<h5>Tags</h5>{/if}
<div class="tags">
<table border="0" cellspacing="0" cellpadding="0">
{section name=tag loop=$tags}
  <tr>
    <td valign="top" style="font-size: .9em"><b>{$tags[tag].keyword}:</b>&nbsp;&nbsp;</td>
    <td class="tag" style="font-size: .9em">{$tags[tag].data|default:"true"}</td>
  </tr>
{/section}
</table>
</div>
{/if}