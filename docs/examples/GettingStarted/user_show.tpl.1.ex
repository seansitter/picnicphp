{if $user}
    Welcome {$user->first_name} {$user->last_name}!
{else}
    No user to show!
{/if}