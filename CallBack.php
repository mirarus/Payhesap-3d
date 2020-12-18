<?php

require 'Payhesap.php'; 

$payhesap = new Payhesap();

$callback = $payhesap->callback();

if ($callback != null) {
	echo "Ödeme Başarılı.<br>";
} else {
	echo "Ödeme Başarısız.<br>";
	echo $payhesap->get_error();
}
?>
<pre><?php print_r($_REQUEST); ?></pre>