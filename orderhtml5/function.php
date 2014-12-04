<?	 if (!function_exists('Hexconvert')) 
{
                					function Hexconvert($hex) {
							$hex = ereg_replace("#", "", $hex);
							$color = array();
							
							if(strlen($hex) == 3) {
								$color['r'] = hexdec(substr($hex, 0, 1) . $r);
								$color['g'] = hexdec(substr($hex, 1, 1) . $g);
								$color['b'] = hexdec(substr($hex, 2, 1) . $b);
							}
							else if(strlen($hex) == 6) {
								$color['r'] = hexdec(substr($hex, 0, 2));
								$color['g'] = hexdec(substr($hex, 2, 2));
								$color['b'] = hexdec(substr($hex, 4, 2));
							}
							return $color;
						}
						function HexToRGB($hex,$name,$transparency,$type) {
							if (strpos($name,'custom') !== false) {
								if (strpos($type,'global') !== false)
								{
									$im = "/custom_images/$_SESSION[appcode]/templates/global_headers/$name?width=320";
								}
								else
								{
									$im = "/custom_images/$_SESSION[appcode]/templates/headers/$name?width=320";
								}
							}
							else if (strpos($name,'mail') !== false)
							{
								$im = "/mob_bizapp/images/".$name;
							}
							else
							{
								$im = "/client/images/templates/global_headers/".$name;
							}
							$imagepath=$im;
							$im = imagecreatefrompng($im);
							$transparency = ((100-($transparency*0.5))*1.27);
							$color = Hexconvert($hex);
							/* R, G, B, so 0, 255, 0 is green */
							if($im && imagefilter($im, IMG_FILTER_COLORIZE, $color['r'], $color['g'], $color['b'],$transparency))
							{
								//echo 'Image successfully shaded green.';
								$outputname = "images/header/".$type.".png";
								//imagepng($im, '../uploads/images/1650/templates/asda.png');
								imagepng($im, $outputname);
								imagedestroy($im);
							}
							else
							{
								echo 'Error converting'.$imagepath;
							}
					 
						}
						
						function RGB_TO_HSV($R, $G, $B)    // RGB values:    0-255, 0-255, 0-255
						{                                // HSV values:    0-360, 0-100, 0-100
							$HSL = array();
							// Convert the RGB byte-values to percentages
							$R = ($R / 255);
							$G = ($G / 255);
							$B = ($B / 255);
							
							$maxRGB = max($R, $G, $B);
							$minRGB = min($R, $G, $B);
							$chroma = $maxRGB - $minRGB;
						
							$computedV = 100 * $maxRGB;
						
							if ($chroma == 0)
							{
								$HSL['H']= "0";
								$HSL['S']= "0";
								$HSL['V']= (5*$computedV);
								return $HSL;
							}
							$computedS = 100 * ($chroma / $maxRGB);
						
							if ($R == $minRGB)
								$h = 3 - (($G - $B) / $chroma);
							elseif ($B == $minRGB)
								$h = 1 - (($R - $G) / $chroma);
							else // $G == $minRGB
								$h = 5 - (($B - $R) / $chroma);
						
							$computedH = 60 * $h;
							$HSL['H'] = $computedH;
						    $HSL['S'] = $computedS;
						    $HSL['V'] = $computedV;
							
							return $HSL;
						}
						
						function HEXTOHSV($hex)
						{
							$RGB = Hexconvert($hex);
							$new_col = RGB_TO_HSV ($RGB[r], $RGB[g], $RGB[b]);
							$dif_col[H] = ($new_col[H])."deg";
							$dif_col[S] = ($new_col[S])."%";
							$dif_col[V] = ($new_col[V])."%";
							return $dif_col;
						}
}
						
						$qrydesignchk["navigation_text_shadow_color"]	= $qrydesignchk["nav_text_alt"];
						$qrydesignchk["navigation_bar_color"]			= $qrydesignchk["global_header_tint"];
						$qrydesignchk["navigation_text_color"]			= $qrydesignchk["nav_text"];
						$qrydesignchk["even_row_color"]			 		= $qrydesignchk["evenrow_bar"];
						$qrydesignchk["even_row_text_color"]			= $qrydesignchk["evenrow_text"];
						$qrydesignchk["odd_row_color"]					= $qrydesignchk["oddrow_bar"];
						$qrydesignchk["odd_row_text_color"]		 		= $qrydesignchk["oddrow_text"];
						$qrydesignchk["section_bar_color"]		 		= $qrydesignchk["section_bar"];
						$qrydesignchk["section_text_color"]	 			= $qrydesignchk["section_text"];
						$launcherheader									= ($qrydesignchk["header_src"] !="no header.png" ? $qrydesignchk["header_src"] : '');
						$global_header									= $qrydesignchk["global_header"];
						if(substr($qrydesignchk["tab_src"],0,6)=='custom') 
								{ 	$tab_src = "/custom_images/$appcode/templates/buttons/$qrydesignchk[tab_src]";}
						else  	{	$tab_src = "../tab_buttons/$qrydesignchk[tab_src]";}
						$show_menu_text									= $qrydesignchk["tab_showtext"];
?>