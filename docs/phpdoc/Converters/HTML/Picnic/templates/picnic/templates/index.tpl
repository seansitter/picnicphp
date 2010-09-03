{include file="header.tpl" eltype="class" hasel=true contents=$classcontents}

<div id="wrapper">
{include file="side.tpl"}

<div id="main-wrapper">
<div id="main">

{if $contents}
{$contents}
{else}
{include file="blank.tpl"}
{/if}

{include file="footer.tpl"}

</div>
</div>
</div>