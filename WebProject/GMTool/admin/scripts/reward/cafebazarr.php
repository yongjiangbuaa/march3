<?php
$msg = date("Y-m-d H:i:s",time()) . '   ' . time() . '  ' . json_encode($_REQUEST);
file_put_contents( "./test.log", $msg . "\n", FILE_APPEND);
echo json_encode($_REQUEST);
?>
