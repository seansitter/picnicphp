{* FOR STATIC METHODS *}
{section name=methods loop=$methods}
{if $methods[methods].static}
{if $show == 'summary'}
static method {$methods[methods].function_call}, {$methods[methods].sdesc}<br />
{else}
  <a name="{$methods[methods].method_dest}"></a>
  <h4 class="method-header">static method {$methods[methods].function_name} <span class="smalllinenumber">[line {if $methods[methods].slink}{$methods[methods].slink}{else}{$methods[methods].line_number}{/if}]&nbsp;&nbsp;[<a href="#top">top</a>]</span></h4>
  
  {*description*}
  {if $methods[methods].desc or $methods[methods].sdesc}
  <div class="desc-container">
  {if $methods[methods].desc != ''}
  <p class="method-description">
    {$methods[methods].desc}
  </p>
  {elseif $methods[methods].sdesc != ''}
  <p class="method-description">
    {$methods[methods].sdesc}
  </p>
  {/if}
  </div>
  {/if}

  {* this is the code looking section *}
  <div class="function">
    <div class="function-sig">
      <code>static {$methods[methods].function_return} {if $methods[methods].ifunction_call.returnsref}&amp;{/if}{$methods[methods].function_name}(
{if count($methods[methods].ifunction_call.params)}
{section name=params loop=$methods[methods].ifunction_call.params}
{if $smarty.section.params.iteration != 1}, {/if}
{if $methods[methods].ifunction_call.params[params].hasdefault}[{/if}{$methods[methods].ifunction_call.params[params].type}
{$methods[methods].ifunction_call.params[params].name}{if $methods[methods].ifunction_call.params[params].hasdefault} = {$methods[methods].ifunction_call.params[params].default}]{/if}
{/section}
{/if})</code>
    </div>

    {if $methods[methods].method_overrides}Overrides {$methods[methods].method_overrides.link}    ({$methods[methods].method_overrides.sdesc|default:"parent method not documented"})<br /><br />
    {/if}
    
    {if count($methods[methods].params) > 0}
    <div class="tags">
      <h5>Parameters</h5>
      <table border="0" cellspacing="0" cellpadding="0">
        {section name=params loop=$methods[methods].params}
        <tr>
          {if $methods[methods].params[params].datatype}<td class="params" valign="top">{/if}<span class="type">{$methods[methods].params[params].datatype}{if $methods[methods].params[params].datatype}</span></td>{/if}
          <td class="params" valign="top"><span class="param">{$methods[methods].params[params].var}</span></td>
          <td class="params" valign="top"><span class="desc">{$methods[methods].params[params].data}</span></td>
        </tr>
        {/section}
      </table>
    </div>
    {/if}
    
    {include showtagheader=true file="docblock.tpl" sdesc=$methods[methods].sdesc desc=$methods[methods].desc tags=$methods[methods].tags}
    
    {if $methods[methods].descmethod}
    <div class="notes">
      <h5>Overridden in child classes as:</h5>
      <div class="notes-data">
        {section name=dm loop=$methods[methods].descmethod}
        <dl>
          <dt>{$methods[methods].descmethod[dm].link}</dt>
          <dd>{$methods[methods].descmethod[dm].sdesc}</dd>
        </dl>
        {/section}
      </div>
    </div>
    {/if}
    
    {if $methods[methods].method_implements}
    <div class="notes">
      <h5>Implementation of:</h5>
      <div class="notes-data">
      {section name=imp loop=$methods[methods].method_implements}
      <dl>
        <dt>{$methods[methods].method_implements[imp].link}</dt>
        {if $methods[methods].method_implements[imp].sdesc}
          <dd>{$methods[methods].method_implements[imp].sdesc}</dd>
        {/if}
      </dl>
      {/section}
      </div>
    </div>
    {/if}
    
  </div>
{/if}
{/if}
{/section}



{* FOR NON-STATIC METHODS *}
{section name=methods loop=$methods}
{if !$methods[methods].static}
{if $show == 'summary'}
method {$methods[methods].function_call}, {$methods[methods].sdesc}<br />
{else}
  <a name="{$methods[methods].method_dest}"></a>
  <h4 class="method-header">{if $methods[methods].ifunction_call.constructor}constructor {elseif $methods[methods].ifunction_call.destructor}destructor {else}method {/if}{$methods[methods].function_name} <span class="smalllinenumber">[line {if $methods[methods].slink}{$methods[methods].slink}{else}{$methods[methods].line_number}{/if}]&nbsp;&nbsp;[<a href="#top">top</a>]</span></h4>
  
  <div class="function">
    <div class="function-sig">
      <code>{$methods[methods].function_return} {if $methods[methods].ifunction_call.returnsref}&amp;{/if}{$methods[methods].function_name}(
{if count($methods[methods].ifunction_call.params)}
{section name=params loop=$methods[methods].ifunction_call.params}
{if $smarty.section.params.iteration != 1}, {/if}
{if $methods[methods].ifunction_call.params[params].hasdefault}[{/if}{$methods[methods].ifunction_call.params[params].type}
{$methods[methods].ifunction_call.params[params].name}{if $methods[methods].ifunction_call.params[params].hasdefault} = {$methods[methods].ifunction_call.params[params].default}]{/if}
{/section}
{/if})</code>
    </div>

{if $methods[methods].method_overrides}Overrides {$methods[methods].method_overrides.link} ({$methods[methods].method_overrides.sdesc|default:"parent method not documented"})<br /><br />{/if}

    {*description*}
    {if $methods[methods].desc or $methods[methods].sdesc}
    <div class="desc-container">
    {if $methods[methods].desc != ''}
    <p class="method-description">
      {$methods[methods].desc}
    </p>
    {elseif $methods[methods].sdesc != ''}
    <p class="method-description">
      {$methods[methods].sdesc}
    </p>
    {/if}
    </div>
    {/if}

    {if count($methods[methods].params) > 0}
    <div class="tags">
    <h5>Parameters</h5>
    <table border="0" cellspacing="0" cellpadding="0">
    {section name=params loop=$methods[methods].params}
      <tr>
          {if $methods[methods].params[params].datatype}<td class="params" valign="top">{/if}<span class="type">{$methods[methods].params[params].datatype}{if $methods[methods].params[params].datatype}</span></td>{/if}
        <td class="params" valign="top"><span class="param">{$methods[methods].params[params].var}</span></td>
        <td class="params" valign="top"><span class="desc">{$methods[methods].params[params].data}</span></td>
      </tr>
    {/section}
    </table>
    </div>
    {/if}
    
    {include file="docblock.tpl" showtagheader=true sdesc=$methods[methods].sdesc desc=$methods[methods].desc tags=$methods[methods].tags}
    
    {if $methods[methods].descmethod}
    <div class="notes">
      <h5>Overridden in child classes as:</h5>
      <div class="notes-data">
        {section name=dm loop=$methods[methods].descmethod}
        <dl>
          <dt>{$methods[methods].descmethod[dm].link}</dt>
          <dd>{$methods[methods].descmethod[dm].sdesc}</dd>
        </dl>
        {/section}
      </div>
    </div>
    {/if}
    
    {if $methods[methods].method_implements}
    <div class="notes">
      <h5>Implementation of:</h5>
      <div class="notes-data">
      {section name=imp loop=$methods[methods].method_implements}
      <dl>
        <dt>{$methods[methods].method_implements[imp].link}</dt>
        {if $methods[methods].method_implements[imp].sdesc}
          <dd>{$methods[methods].method_implements[imp].sdesc}</dd>
        {/if}
      </dl>
      {/section}
      </div>
    </div>
    {/if}
    
  </div>
{/if}
{/if}
{/section}
