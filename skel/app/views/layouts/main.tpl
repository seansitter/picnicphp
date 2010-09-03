{display_doctype type='XHTML1_STRICT'}
<html>
<head>
{display_js_links}
{display_css_links}
<title>
{if $site_title}{$site_title}{/if}
{if $site_title and $page_title} :: {/if}
{$page_title}
</title>
</head>
<body>
-- this is the layout found in app/views/layouts/main.tpl<br/>
{display_alerts}

{display_layout_body}

</body>
</html>