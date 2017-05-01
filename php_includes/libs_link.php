<?php 

function printJSLibs(){
	$js_libs = [
		'jquery-1.11.2.min.js',
		'bootstrap.min.js'
	];	
	for($i=0;$i<count($js_libs);$i++){
		echo '<script type="text/javascript" src="lib/'.$js_libs[$i].'"></script>';
	}
}



function printCSSLibs(){
	$css_lib = [
		'bootstrap.min.css',
		'font-awesome.min.css'
	];
	for($i=0;$i<count($css_lib);$i++){
		echo '<link src="lib/'.$css_lib[$i].'"/>';
	}
}


?>