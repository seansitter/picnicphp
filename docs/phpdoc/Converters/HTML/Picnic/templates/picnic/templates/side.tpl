<div id="side">
  {if $tutorials}
    <div class="section-header">Documentation</div>
    {if $tutorials.pkg}
      {section name=ext loop=$tutorials.pkg}
        {$tutorials.pkg[ext]}
      {/section}
    {/if}
  {/if}
  
  {if !$noleftindex}
    {* no files section
    {if $compiledfileindex}
      <b>Files:</b><br />
      {eval var=$compiledfileindex}
    {/if}
    *}

    {if $compiledinterfaceindex or $compiledclassindex}
      <div class="section-header">API Reference</div>
      {if $compiledclassindex}
        <div class="subsection-header">Classes:</div>
        {eval var=$compiledclassindex}
      {/if}
      <div style="margin-top: 10px">
      {if $compiledinterfaceindex}
        <div class="subsection-header">Interfaces:</div>
        {eval var=$compiledinterfaceindex}
      {/if}
      </div>
    {/if}
  {/if}
</div>

{*
<table border="0" cellspacing="0" cellpadding="0" height="48" width="100%">
  <tr>
    <td class="header_top">{$package}</td>
  </tr>
  <tr><td class="header_line"><img src="{$subdir}media/empty.png" width="1" height="1" border="0" alt=""  /></td></tr>
  <tr>
    <td class="header_menu">
        {assign var="packagehaselements" value=false}
        {foreach from=$packageindex item=thispackage}
            {if in_array($package, $thispackage)}
                {assign var="packagehaselements" value=true}
            {/if}
        {/foreach}
        {if $packagehaselements}
  		  [ <a href="{$subdir}classtrees_{$package}.html" class="menu">class tree: {$package}</a> ]
		  [ <a href="{$subdir}elementindex_{$package}.html" class="menu">index: {$package}</a> ]
		{/if}
  	    [ <a href="{$subdir}elementindex.html" class="menu">all elements</a> ]
    </td>
  </tr>
  <tr><td class="header_line"><img src="{$subdir}media/empty.png" width="1" height="1" border="0" alt=""  /></td></tr>
</table>
*}


{if count($ric) >= 1}
<div id="ric">
  {section name=ric loop=$ric}
    <p><a href="{$subdir}{$ric[ric].file}">{$ric[ric].name}</a></p>
  {/section}
</div>
{/if}

