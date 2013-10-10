<?php
if ($e instanceof Exception) {
	printf(
		'<p>Exception "%s" with message "%s" at %s line %u</p><pre>%s</pre>',
		get_class($e), $e->getMessage(), $e->getFile(), $e->getLine(), $e->getTraceAsString()
	);
}
?>
