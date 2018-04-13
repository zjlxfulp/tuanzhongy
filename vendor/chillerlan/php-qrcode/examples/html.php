<?php

require_once '../vendor/autoload.php';

use chillerlan\QRCode\Output\QRMarkup;
use chillerlan\QRCode\Output\QRMarkupOptions;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;

//---------------------------------------------------------

echo '<style>

.qrcode,
.qrcode > p,
.qrcode > p > b,
.qrcode > p > i {
	margin:0;
	padding:0;
}

/* row element */
.qrcode > p {
	height: 1.25mm;
	display: block;
}

/* column element(s) */
.qrcode > p > b, .qrcode > p > i{
	display: inline-block;
	width: 1.25mm;
	height: 1.25mm;
}

.qrcode > p > b{
	background-color: #000;
}

.qrcode > p > i{
	background-color: #fff;
}

</style>';

$qrStringOptions = new QRMarkupOptions;
$qrStringOptions->type = QRCode::OUTPUT_MARKUP_HTML;

$qrOptions = new QROptions;
$qrOptions->typeNumber = QRCode::TYPE_05;
$qrOptions->errorCorrectLevel = QRCode::ERROR_CORRECT_LEVEL_L;

$qr = new QRCode('skype://callto:echo123', new QRMarkup($qrStringOptions), $qrOptions);

echo '<div class="qrcode">'.$qr->output().'</div>';
