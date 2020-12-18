<?php

require 'Payhesap.php'; 

$payhesap = new Payhesap();

$callback = $payhesap->callback();

if ($callback == null) {
	echo "Ödeme Başarısız.<br>";
	echo $payhesap->get_error();
} else {
	echo "Ödeme Başarılı.<br>";
}