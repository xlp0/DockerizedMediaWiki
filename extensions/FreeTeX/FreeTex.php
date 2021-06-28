<?php

$wgExtensionFunctions[] = "wfFreeTex";

function wfFreeTex() {
	global $wgParser;
	$wgParser->setHook("tex", "returnTexImg");
}

function returnTexImg($code, $argv) {
	$density = 100;
	$quality = 100;
	if($argv["density"] and intval($argv["density"] > 0))
		$density = intval($argv["density"]);
	if($argv["quality"] and intval($argv["quality"] > 0))
		$quality = intval($argv["density"]);
	$hash = md5(sprintf("%s-%d-%d", $code, $quality, $density));
	file_put_contents($hash.".tex", $code);
	$cmd_gen = "pdflatex ".$hash.".tex";
	$cmd_cvt = sprintf("convert -quality %d -density %d ".$hash.".pdf /xlp_data/images/teximg/".$hash.".png", $quality, $density);
	$cmd_gen_return = "";
	exec($cmd_gen, $cmd_gen_return);
	exec($cmd_cvt);
	if(file_exists("/xlp_data/images/teximg/".$hash.".png"))
		$url_return = '<img src="/images/teximg/'.$hash.'.png">';
	else if(file_exists($hash.".pdf") and file_exists("/xlp_data/images/teximg/".$hash."-0.png")) {
		#$url_return = "<h3>Error: The document has multiple pages</h3>";
		$url_return = '<img src="/images/teximg/'.$hash.'-0.png">';
		$pic_index = 1;
		while(file_exists(sprintf("/xlp_data/images/teximg/".$hash."-%d.png", $pic_index))) {
			$url_return = $url_return.sprintf('<br><img src="/images/teximg/%s-%d.png">', $hash, $pic_index);
			$pic_index = $pic_index + 1;
		}
	}
	else
		$url_return = '<h3 style="color: #ff0000;">LaTeX Compile Error:</h3><p>'.implode("<br>", $cmd_gen_return)."</p>";
	exec("rm ".$hash.".aux");
	exec("rm ".$hash.".pdf");
	exec("rm ".$hash.".tex");
	exec("rm ".$hash.".log");
	return $url_return;
}

?>
