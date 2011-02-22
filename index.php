<?php
// Undo magic quotes
if (get_magic_quotes_gpc()) $_REQUEST = array_map('stripslashes', $_REQUEST);

// Get content
$content = isset($_REQUEST['content']) ? $_REQUEST['content'] : null;

// Grab the actual content from the page.
$fixedContent = getWikiContent($content);

// Substitute some Confluence HTML
$replacements = array(
  '@<span class="nobr"><a@' => '<a',
  '@<sup><img class="rendericon" src="/images/icons/linkext7.gif" height="7" width="7" align="absmiddle" alt="" border="0"/></sup></a></span>@' => '</a>',
	'@\srel="nofollow"@' => '',
	'@<h(\d)><a name="([^"]+)"></a>([^<]+)</h\d>@' => '<h\1 id="\2">\3</h\1>');
$fixedContent = preg_replace(array_keys($replacements), array_values($replacements), $fixedContent);

// Fix code blocks
$fixedContent = preg_replace_callback('@<div class="code panel" style="border-width: 1px;"><div class="codeContent panelContent">\s*<pre class="code-(\w+)">(.*)</pre>\s+</div></div>@msU', "replaceCodeBlocks", $fixedContent);

// Trim whitespaces
$fixedContent = trim($fixedContent);

function getWikiContent($content) {
	$from = "<!-- wiki content -->";
	$fromOffset = strlen($from);
	$to = "<rdf:";
	$toOffset = -10;
	$start = strpos($content, $from) + $fromOffset;
	$end = strpos($content, $to) + $toOffset;
	return substr($content, $start, $end - $start);
}

function replaceCodeBlocks($matches) {
	$lang = $matches[1];
	
	if(strlen($lang) == 0) {
		$brush = null;
	} else {
		$brush = "brush: " . $lang . "; ";
	}
	 
	$code = $matches[2];
	
	$code = trim($code);
	$code = strip_tags($code);
	$code = $code;
	return <<<eoc
<div class="code">
 <pre class="{$brush}gutter: false">{$code}</pre>
</div>
eoc;
}
?>
<html>
  <head>
    <title>Drupalify</title>
  </head>
  <body>
    <form method="post">
    <h1>Drupalify</h1>
    <p>Paste the entire HTML source from the confluence page of the article, hit submit and put the output in Drupal or complain to Erik if it fails.</p>
    <textarea name="content" style="width: 600px; height: 200px;"><?=htmlspecialchars($content)?></textarea>
    <input type="submit" />
    </form>
  </body>
  <pre><?=htmlspecialchars($fixedContent)?></pre>
</html>