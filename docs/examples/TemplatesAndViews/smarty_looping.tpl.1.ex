Hello, {$user->name},
{if $user->name == 'sammy'}How are ya old pal?{else}Nice to meet you!{/if}<br/>
how are the kids:<br/>
{foreach from=$user->kids item="kid"}
{$kid},<br/>
{foreachelse}
you have no kids!
{/foreach}
