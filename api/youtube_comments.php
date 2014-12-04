<?php

	include_once "xml2array.php";

	function gzdecode($data) { 
		$len = strlen($data); 
		
		if ($len < 18 || strcmp(substr($data,0,2),"\x1f\x8b")) { 
			return null;  // Not GZIP format (See RFC 1952) 
		} 

		$method = ord(substr($data,2,1));  // Compression method 
		$flags  = ord(substr($data,3,1));  // Flags 
		
		if ($flags & 31 != $flags) { 
			// Reserved bits are set -- NOT ALLOWED by RFC 1952 
			return null; 
		}
		
		// NOTE: $mtime may be negative (PHP integer limitations) 
		$mtime = unpack("V", substr($data,4,4)); 
		$mtime = $mtime[1]; 
		$xfl   = substr($data,8,1); 
		$os    = substr($data,8,1); 
		$headerlen = 10; 
		$extralen  = 0; 
		$extra     = ""; 
		
		if ($flags & 4) { 
			// 2-byte length prefixed EXTRA data in header 
			if ($len - $headerlen - 2 < 8) { 
				return false;    // Invalid format 
			} 
			
			$extralen = unpack("v",substr($data,8,2)); 
			$extralen = $extralen[1]; 
			if ($len - $headerlen - 2 - $extralen < 8) { 
				return false;    // Invalid format 
			} 

			$extra = substr($data,10,$extralen); 
			$headerlen += 2 + $extralen; 
		} 

		$filenamelen = 0; 
		$filename = ""; 
		if ($flags & 8) { 
			// C-style string file NAME data in header 
			if ($len - $headerlen - 1 < 8) { 
				return false;    // Invalid format 
			} 
			$filenamelen = strpos(substr($data,8+$extralen),chr(0)); 
			if ($filenamelen === false || $len - $headerlen - $filenamelen - 1 < 8) { 
				return false;    // Invalid format 
			} 
			$filename = substr($data,$headerlen,$filenamelen); 
			$headerlen += $filenamelen + 1; 
		} 

		$commentlen = 0; 
		$comment = ""; 
		if ($flags & 16) { 
			// C-style string COMMENT data in header 
			if ($len - $headerlen - 1 < 8) { 
				return false;    // Invalid format 
			} 
			$commentlen = strpos(substr($data,8+$extralen+$filenamelen),chr(0)); 
			if ($commentlen === false || $len - $headerlen - $commentlen - 1 < 8) { 
				return false;    // Invalid header format 
			} 
			$comment = substr($data,$headerlen,$commentlen); 
			$headerlen += $commentlen + 1; 
		} 

		$headercrc = ""; 
		if ($flags & 1) { 
			// 2-bytes (lowest order) of CRC32 on header present 
			if ($len - $headerlen - 2 < 8) { 
				return false;    // Invalid format 
			} 
			$calccrc = crc32(substr($data,0,$headerlen)) & 0xffff; 
			$headercrc = unpack("v", substr($data,$headerlen,2)); 
			$headercrc = $headercrc[1]; 
			if ($headercrc != $calccrc) { 
				return false;    // Bad header CRC 
			} 
			$headerlen += 2; 
		} 

		// GZIP FOOTER - These be negative due to PHP's limitations 
		$datacrc = unpack("V",substr($data,-8,4)); 
		$datacrc = $datacrc[1]; 
		$isize = unpack("V",substr($data,-4)); 
		$isize = $isize[1]; 

		// Perform the decompression: 
		$bodylen = $len-$headerlen-8; 
		if ($bodylen < 1) { 
			// This should never happen - IMPLEMENTATION BUG! 
			return null; 
		} 
		$body = substr($data,$headerlen,$bodylen); 
		$data = ""; 
		if ($bodylen > 0) { 
			switch ($method) { 
				case 8: 
					// Currently the only supported compression method: 
					$data = gzinflate($body); 
					break; 
				default: 
					// Unknown compression method 
					return false; 
			} 
		} else { 
			// I'm not sure if zero-byte body content is allowed. 
			// Allow it for now...  Do nothing... 
		} 

		// Verifiy decompressed size and CRC32: 
		// NOTE: This may fail with large data sizes depending on how 
		//       PHP's integer limitations affect strlen() since $isize 
		//       may be negative for large sizes. 
		if ($isize != strlen($data) || crc32($data) != $datacrc) { 
			// Bad format!  Length or CRC doesn't match! 
			return false; 
		} 
		return $data; 
	} 

	function refineDate($datev) {
		//http://www.kinderfonds.nl/centraal/rss_nieuws
		$pubDate = strtotime($datev);
		if($pubDate) {
			return $datev;
		} else {
			
			$time_info = "";
			if(preg_match("/(\d{2}:\d{2}:\d{2})/", $datev, $matches)) {
				$time_info = substr($matches[0], 0, 8);
			}
			
			$date_info = "";
			if(preg_match("/(\d{4}-\d{2}-\d{2})/", $datev, $matches)) {
				$date_info = substr($matches[0], 0, 10);
			}
			
			if(($date_info == "") && ($time_info != "")) {
				$date_info = trim(substr($datev, 0, strpos($datev, $time_info)));
			}
			
			
			
			/*
			$time_zone = "";
			if(preg_match("/([+-]{1})(\d{2}:\d{2})/", $datev, $matches)) {
				$time_info = "GMT".substr($matches[0], 0, 5);
			}
			*/
			
			if(($date_info != "") && ($time_info != "")) {
				$datev = $date_info." ".$time_info; //." ".$time_zone;
			}
		}
		return $datev;
	}

	function fetchRSSfromURL( $url ) {
		$feed = array();
		// -----------------------------------------------
		// Create context
		// -----------------------------------------------
		$opts = array(
		  'http'=>array(
			'method'=>"GET",
			'header'=>"User-Agent:Mozilla/5.0 (Windows NT 6.1) AppleWebKit/535.1 (KHTML, like Gecko) Chrome/14.0.835.186 Safari/535.1"
		  )
		);
		
		$another_opts = array(
			'http'=>array(
				'method'=>"GET",
				'header'=>"
					Accept:text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8
					Accept-Charset:ISO-8859-1,utf-8;q=0.7,*;q=0.3
					User-Agent:Mozilla/5.0 (Windows NT 6.1) AppleWebKit/535.1 (KHTML, like Gecko) Chrome/14.0.835.186 Safari/535.1"
			  )
		);
		
		// -----------------------------------------------
		// Read RSS from the source
		// -----------------------------------------------
		
		$context = stream_context_create($opts);
		$rss = file_get_contents($url, false, $context);
		if($rss == "") {
			$context = stream_context_create($another_opts);
			$rss = file_get_contents($url, false, $context);
		}

		if(gzdecode($rss)) {
			$rss = gzdecode($rss);
		}

		
		// ------------------------------------------------------------------------------------------
		// Ray added to replace html tag which was not produced exactly...
		// ------------------------------------------------------------------------------------------
	  
		$issues = array(
		'<em>', '</em>', 
		  '<br/>', '<br>', '<br/>', '<br />', 
		  '<a ', '</a>', 
		  '<i>', '</i>' , 
		  '<div>', '</div>' , '<div ', 
		  '<span>', '</span>' , '<span ', 
		  '<img ', '</img>', 
		  '<p>', '</p>' ,  '<p ', 
		  '<ol>', '</ol>', '<ol ', 
		  '<li>', '</li>', '<li ', 
		  '<ul>', '</ul>', '<ul ' ,
		  '<strong>', '</strong>', '<strong ' ,
		  '<sup>', '</sup>', '<sup ' ,
		  '<blockquote>', '</blockquote>', '<blockquote ',
		  '<iframe>', '</iframe>', '<iframe '
		);

		$subrulsFrom = array('<', '>');
		$subrulsTo = array('&lt;', '&gt;');

		$settles = array();

		foreach($issues AS $value) {
			$v = str_replace($subrulsFrom, $subrulsTo, $value);
			$settles[] = $v;
		}

		$rss = str_replace($issues, $settles, $rss);
	  
		// -- End
	  
		$entries = array();
	
		$array = xml2array($rss);	  

		if ( $array['feed']['entry'] ) {
			foreach ( $array['feed']['entry'] as $data ) {
				if(isset($data['id'])) {
					$feed = array();
					$id_spilits = explode(':', $data['id']);
					$feed['id'] = $id_spilits[count($id_spilits) - 1];

					$feed['published'] = strtotime($data['published']);
					$feed['title'] = $data['title'];
					$feed['content'] = $data['content'];
					$feed['author'] = $data['author']['name'];
					$feed['author_id'] = $data['author']['yt:userId'];
					$feed['video_id'] = $data['yt:videoid'];

					$entries[] = $feed;
				}
			}
		}

		return $entries;
	}

	$video_id = $_GET['id'];
    
    $return = array();

	$result = array();
	if ( !$video_id ) {
		$result['error'] = 'Invalid video id.';
	}

	$start_index = intval( $_GET['startindex'] );
	if ( !$start_index )
		$start_index = 1;

    $orderby = $_GET['orderby'];
    if ( !$orderby || !in_array( $orderby, array('popular', 'featured', 'recent') ) )
        $orderby = 'recent';
        
    if ( $orderby == 'popular ')
        $orderby = 'viewCount';
    else if ( $orderby == 'featured ')
        $orderby = 'rating';
    else
        $orderby = 'published';
    
	$url = 'https://gdata.youtube.com/feeds/api/videos/' . $video_id . '/comments?max-results=50&start-index=' . $start_index . '&orderby=' . $orderby . '&v=2';

	$result = fetchRSSfromURL($url);
    
    $new_result = array();
    
    if ( !$result ) {
        $new_result['error'] = 'No comments.';
    } else {
        foreach ( $result as $row ) {
            if ( $row['id'] && $row['content'] && !is_array($row['content']) ) {

                $author_result = json_decode( file_get_contents('https://gdata.youtube.com/feeds/api/users/' . $row['author_id']. '?fields=media:thumbnail&alt=json'), true );
                $author_thumbnail = $author_result['entry']['media$thumbnail']['url'];
                $row['author_avatar'] = $author_thumbnail;
                
                $new_result[] = $row;
            }
        }
    }
    
    $return[] = $new_result;

	$json = json_encode($return);

	header("Content-encoding: gzip");
	echo gzencode($json);
	exit;

?>