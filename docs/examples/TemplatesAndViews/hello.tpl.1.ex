List of post comments:
{foreach from=$post->comments item="comment"}
    {include file="shared/comment.tpl" user=$commentor comment=$comment}
{/foreach}