<?php 

include './Half_tone.php';

// single image test
if (false){	
	$img_source = 'test.jpg';
	$output_source = 'test_out.jpg';
	$obj = new Half_tone($img_source);
	$obj->create_half_tone_image();
	$obj->save_to_file($output_source);
}


// run with multiple block and ratio_const sizes
if (false){
	$s_counter =0;
	$r_counter =0;

	for ($s=3; $s<10; $s+=2) { // sizes 3,5,7

		$s_counter++;
		$r_counter =0;

		for ($r=-2; $r<=2; $r+=0.1) { // ratio constant

			$r_counter++;	
			$obj = new Half_tone('bike.jpg');
			$obj->set_block_size($s);
			$obj->set_ratio_const($r);
			$obj->create_half_tone_image();
			$obj->save_to_file('out_'.$s_counter.'_'.($r_counter<10?'0'.$r_counter:$r_counter).'.jpg');
		}
	}
}

// run multiple versions of imagic
if (false){
	$arr = array('o4x4','o4x4','o4x4,3,3','o4x4,8,8,8','o8x8','o8x8,3','o8x8,6,6','h8x8a','h8x8a,3,3','h8x8a,8,8',	'checks','checks,3,3','checks,8,8');
	foreach ($arr as $key => $value) {		
		orderedPosterizeImage('test.jpg', 'out'.$value.'.jpg', $value);
	}	
}

function orderedPosterizeImage($in_image, $out_image, $orderedPosterizeType)
	{
	    $imagick = new Imagick(realpath($in_image));
	    $imagick->orderedPosterizeImage($orderedPosterizeType);
		$imagick->setImageFormat('jpg');
	    $imagick->writeImages($out_image, true);
	}

if (false){
	orderedPosterizeImage('test.jpg', 'test_out.jpg', 'h8x8a,8,8');
}
?>


