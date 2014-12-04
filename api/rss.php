<?php

/**
 * rss.php
 * This api is called when loading podcast, rss and youtube tab.
 * @params: app_code, device, tab_id, orderby, timezone
 * Modified by Daniel 4/24/2014
 */

include_once "dbconnect.inc";
include_once "app.inc";

include_once "xml2array.php";

if ( !function_exists('gzdecode') ) {
    /**
    * Decodes a gzip compressed string
    */
    function gzdecode($data) {

        $len = strlen($data); 
        if ( $len < 18 || strcmp(substr($data,0,2),"\x1f\x8b") ) { 
            return null;  // Not GZIP format (See RFC 1952) 
        }

        $method = ord(substr($data,2,1));  // Compression method
        $flags  = ord(substr($data,3,1));  // Flags
        if ( $flags & 31 != $flags ) {
            // Reserved bits are set -- NOT ALLOWED by RFC 1952
            return null; 
        }

        // NOTE: $mtime may be negative (PHP integer limitations)
        $mtime = unpack("V", substr($data,4,4));
        $mtime = $mtime[1];
        $xfl = substr($data,8,1);
        $os = substr($data,8,1);
        $headerlen = 10;
        $extralen = 0;
        $extra = "";
        
        if ( $flags & 4 ) {
            // 2-byte length prefixed EXTRA data in header
            if ($len - $headerlen - 2 < 8) {
                return false;    // Invalid format
            }

            $extralen = unpack("v",substr($data,8,2));
            $extralen = $extralen[1];
            if ( $len - $headerlen - 2 - $extralen < 8 ) {
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
}

/**
* Get correct date time format
*/
function refineDate($datev) {
    $pubDate = strtotime($datev);
    if ( $pubDate ) {
        return $datev;
    } else {
        
        $time_info = "";
        if ( preg_match("/(\d{2}:\d{2}:\d{2})/", $datev, $matches) ) {
            $time_info = substr($matches[0], 0, 8);
        }
        
        $date_info = "";
        if ( preg_match("/(\d{4}-\d{2}-\d{2})/", $datev, $matches) ) {
            $date_info = substr($matches[0], 0, 10);
        }
        
        if ( ($date_info == "") && ($time_info != "") ) {
            $date_info = trim(substr($datev, 0, strpos($datev, $time_info)));
        }
         
        if ( ($date_info != "") && ($time_info != "" )) {
            $datev = $date_info  ." " . $time_info; //." ".$time_zone;
        }
    }

    return $datev;
}

/**
* Get timezone from the date
*/
function getTimezoneFromDate($datev) {
    $timezone = 0;
    
    if ( $datev ) {
        $datearray = split(' ', $datev);
        if ( $datearray[5] ) {
            $timezone = strtoupper( trim($datearray[5]) );
            
            if ( $timezone == 'EST' ) {
                $timezone = '-0500';
            } else if ( $timezone == 'EDT' ) {
                $timezone = '-0400';
            } else if ( $timezone == 'CST' ) {
                $timezone = '-0600';
            } else if ( $timezone == 'ATC' ) {
                $timezone = '-0400';
            } else if ( $timezone == 'MST' ) {
                $timezone = '-0700';
            } else if ( $timezone == 'PST' ) {
                $timezone = '-0800';
            } else if ( $timezone == 'AKST' ) {
                $timezone = '-0900';
            }
                
            $timezone_new = '';
            for ($i = 0; $i < strlen($timezone); $i++) {
                if ( $timezone[$i] != '0' ) {
                    $timezone_new .= $timezone[$i];
            }
            }
            $timezone = $timezone_new;
                
        }
    }
        
    return $timezone;
}

/**
* Check if it is image by name
*/
function isImage_byname( $url ) {
    $pos = strrpos( $url, ".");
    if ($pos === false) {
        return false;
    }
    
    $ext = strtolower(trim(substr( $url, $pos)));
    $imgExts = array(".gif", ".jpg", ".jpeg", ".png", ".tiff", ".tif"); // this is far from complete but that's always going to be the case...
    
    if ( in_array($ext, $imgExts) ) {
        return true;
    }

    return false;
}

/**
* Fetch rss feed from the url
*/
function fetchRSSfromURL($url, $web_url, $rsstype, $emptyimage = false) {
    $feed = array();
    
    // -----------------------------------------------
    // Create context
    // -----------------------------------------------
    $opts = array(
        'http' => array(
            'method' => "GET",
            'header' => "User-Agent:Mozilla/5.0 (Windows NT 6.1) AppleWebKit/535.1 (KHTML, like Gecko) Chrome/14.0.835.186 Safari/535.1"
        )
    );
    
    $another_opts = array(
        'http'=>array(
            'method' => "GET",
            'header' => "
            Accept:text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8
            Accept-Charset:ISO-8859-1,utf-8;q=0.7,*;q=0.3
            User-Agent:Mozilla/5.0 (Windows NT 6.1) AppleWebKit/535.1 (KHTML, like Gecko) Chrome/14.0.835.186 Safari/535.1"
        )
    );
    
    // -----------------------------------------------
    // Read RSS from the source
    // -----------------------------------------------
    
    if ( strpos($url, 'www.facebook.com/feeds/page.php') !== false ) {

        $url_pattern = parse_url($url);
        $url_query = str_replace('&amp;', '&', $url_pattern['query']);
        $url_queries = explode('&', $url_query);
        
        if ( $url_queries ) {
            $fb_page_id = '';
            foreach ( $url_queries as $v ) {
                $pair = explode('=', $v);
                if ( $pair[0] == 'id' ) {
                    $fb_page_id = $pair[1];
                    break;
                }
            }

            if ( $fb_page_id ) {
                require_once "facebook/facebook.php";

                $config = array();
                $config['appId'] = '1446655168924609';
                $config['secret'] = '30ccb4ec06c923b007e7a021946bb5f9';
                $config['fileUpload'] = false;
                $facebook = new Facebook($config);
                $pageid = "49688188545";
                $pagefeed = $facebook->api("/" . $fb_page_id . "/feed");
                $useful = $pagefeed['data'];

                foreach ($useful as $data) {

                    $data["title"] = "No title";
                    $data["pubDate"] = refineDate($data["created_time"]);
                    $pubDate = $data["pubDate"];
                    $section = date("m/d/Y", $pubDate);
                    $data["timezone"] = '0';
                    $creator = $data["from"]["name"];

                    $imageurl = '';
                    if ( $data["picture"] ) {
                        $imageurl = $data["picture"];
                    }

                    if ( $data["message"] ) {
                        $content = $data["message"];
                    } else if ( $data["story"] ) {
                        $content = $data["story"];
                    } else {
                        $content = '';
                    }

                    if ( !$data['title'] && !$data['pubDate'] && !$data["link"] ) {
                        continue;
                    }

                    // Ray added - multi-byte safe cutting.
                    $summary = mb_substr(strip_tags(html_entity_decode(htmlentities($content))), 0, 250, 'UTF-8');
                    $summary = preg_replace("/&#?[a-z0-9]{2,8};/i","",$summary); // Remove HTML Special chars, like &#8220;

                    $description = "<p><b>$data[title]</b><br><font color=grey>".date("l F j Y", $pubDate)."</font></p>".$content;
                    if ( $data["link"] ) {
                        $description .= "<p><a href=\"$data[link]\"><img src=\"http://www.appsomen.com/images/rssfeed.png\"></a></p>";
                    }

                    $newEl = array(
                        "id" => $idv,  // Needed to make cell tappable; actually irrelevant
                        "title" => $data["title"],
                        "imageurl" => $imageurl,
                        "description" => $description,
                        "summary" => $summary,
                        "creator" => $creator,
                        "link" => $data["link"],
                        "pubDate" => $pubDate,
                        "timezone" => $data["timezone"],
                        "section" => $section
                    );

                    $feed[] = $newEl;
                }

            }
        }

    } else {    
        $context = stream_context_create($opts);
        $rss = file_get_contents($url, false, $context);
            
        if ( $rss == "" ) {
            $context = stream_context_create($another_opts);
            $rss = file_get_contents($url, false, $context);
        }

        if ( function_exists('gzdecode') ) {
            if ( gzdecode($rss) ) {
                $rss = gzdecode($rss);
            }
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

        foreach ( $issues AS $value ) {
            $v = str_replace($subrulsFrom, $subrulsTo, $value);
            $settles[] = $v;
        }

        $rss = str_replace($issues, $settles, $rss);

        $array = xml2array($rss);
      
        $imageurl = '';

        if ( is_array($array["rss"]) ) {
            $useful = $array["rss"]["channel"]["item"];
            if ( is_array($array["rss"]["channel"]["image"]) && isset($array["rss"]["channel"]["image"]["url"]) ) {
                if ( $emptyimage == false ) {
                    $imageurl = $array["rss"]["channel"]["image"]["url"];
                }
            }
        } else if ( is_array($array["feed"]) ) {
            $useful = $array["feed"]["entry"];
        } else if ( is_array($array["rdf:RDF"]) ) {
            $useful = $array["rdf:RDF"]["item"];
        } else if ( is_array($array["entry"]) ) {
            $useful = $array["entry"];
        }
       
        // Ray added for single element RSS data
        if ( (count($useful) > 0) && !isset($useful[0]) ) {
            $newUseful = array($useful);
            $useful = $newUseful;
        } // End

        foreach ($useful as $data) {

            $idv = 1;

            if ( is_array($data["title"]) )
                $data["title"] = "No title";

            if ( !isset($data["pubDate"]) ) { // If publish date is empty, then try to discover it.
                if ( isset($data["published"]) ) {
                    $data["pubDate"] = $data["published"];
                } else if ( isset($data["dc:date"]) ) {
                    $data["pubDate"] = $data["dc:date"];
                } else if ( isset($data["startdate"]) ) {
                    $data["pubDate"] = $data["startdate"];
                }
            }
     
            $data["pubDate"] = refineDate($data["pubDate"]);
            $pubDate = strtotime($data["pubDate"]);
            if ( $pubDate && $pubDate > 0 ) {
                $section = date("m/d/Y", $pubDate);
            } else {
                $pubDate = "";
                $section = "";
            }

            $timezone = getTimezoneFromDate($data["pubDate"]);
            $data["timezone"] = $timezone;

                if ( isset($data["dc:creator"]) ) {
                $creator = $data["dc:creator"];
                } else if ( isset($data["author"]) && is_array($data["author"]) ) {
                $creator = $data["author"]["name"];
                } else if ( isset($data["author"]) ) {
                $creator = $data["author"];
                } else {
                $creator = "Admin";
                }

            if ( $data["description"] ) {
                $content = $data["description"];
            } else if ( $data["content"] ) {
                $content = $data["content"];
            } else {
                $content = '';
            }

                if ( !$data['title'] && !$data['pubDate'] && !$data["link"] ) {
                continue;
                }

            $content = str_replace($settles, $issues, $content);

            // Ray added - multi-byte safe cutting.
            $summary = mb_substr(strip_tags(html_entity_decode(htmlentities($content))), 0, 250, 'UTF-8');
            $summary = preg_replace("/&#?[a-z0-9]{2,8};/i","",$summary); // Remove HTML Special chars, like &#8220;

            $description = "<p><b>$data[title]</b><br><font color=grey>".date("l F j Y", $pubDate)."</font></p>".$content;
            if ( $data["link"] ) {
                $description .= "<p><a href=\"$data[link]\"><img src=\"http://www.appsomen.com/images/rssfeed.png\"></a></p>";
            } else if ( $data["link_attr"] ) {
                if ( $data["link_attr"]["href"] ) {
                    $description .= '<p><a href="'.$data["link_attr"]["href"].'"><img src="http://www.appsomen.com/images/rssfeed.png"></a></p>';
                }
            }

            $videoid_spilits = explode(':', $data['id']);
            $video_id = $videoid_spilits[count($videoid_spilits) - 1];

            if ( ereg("gdata.youtube.com", $web_url) ) {
                if ( ereg("<span>([^<]*)</span>", $data["description"], $regs) ) {
                    $summary = $regs[1];
                } else {
                    $summary = "Tap to watch the video on YouTube";
                }

            $description = <<< EOF
<div align=center style="font-family: helvetica, sans-serif; font-size: 12pt;">
$data[title]
<br>
<br>
<embed src="http://www.youtube.com/v/$video_id&;hl=en&fs=1&rel=0&showinfo=0" type="application/x-shockwave-flash" allowscriptaccess="always" allowfullscreen="true" width="280" height="140"></embed>
<p>$summary</p>
</div>
EOF;
            }

            // Ray added
            $additional = array();
            if ( ereg("gdata.youtube.com", $web_url) ) {

                $additional["published"] = $data["published"];
                $additional["gd:feedlink_href"] = "";
                $additional["gd:feedlink_countHint"] = "";
                
                if ( is_array($data["gd:comments"]) && is_array($data["gd:comments"]["gd:feedLink_attr"]) ) {
                        if ( isset($data["gd:comments"]["gd:feedLink_attr"]["href"]) ) {
                        $additional["gd:feedlink_href"] = $data["gd:comments"]["gd:feedLink_attr"]["href"];
                        }
                        if ( isset($data["gd:comments"]["gd:feedLink_attr"]["countHint"]) ) {
                        $additional["gd:feedlink_countHint"] = $data["gd:comments"]["gd:feedLink_attr"]["countHint"];
                }
                    }

                $additional["media:thumbnail_url"] = "";
                if ( is_array($data["media:group"]) && is_array($data["media:group"]["media:thumbnail"]) ) {
                    if ( is_array($data["media:group"]["media:thumbnail"]["0_attr"]) && isset($data["media:group"]["media:thumbnail"]["0_attr"]["url"]) ) {
                        $additional["media:thumbnail_url"] = $data["media:group"]["media:thumbnail"]["0_attr"]["url"];
                        $additional["imageurl"] = $additional["media:thumbnail_url"];
                    }
                }

                $additional["gd:rating_average"] = "";
                if ( is_array($data["gd:rating_attr"]) && isset($data["gd:rating_attr"]["average"]) ) {
                    $additional["gd:rating_average"] = $data["gd:rating_attr"]["average"];
                }

                $additional["numlikes"] = $additional["numdislikes"] = "";
                if ( is_array($data["yt:rating_attr"]) ) {
                    $additional["numlikes"] = $data["yt:rating_attr"]['numLikes'];
                    $additional["numdislikes"] = $data["yt:rating_attr"]['numDislikes'];
                }

                $additional["yt:statistics_viewCount"] = "";
                if ( is_array($data["yt:statistics_attr"]) && isset($data["yt:statistics_attr"]["viewCount"]) ) {
                    $additional["yt:statistics_viewCount"] = $data["yt:statistics_attr"]["viewCount"];
                }        

                if ( is_array($data["link"]) ) {
                    foreach ( $data["link"] AS $v ) {
                        if ( is_array($v) && isset($v["rel"]) && ($v["rel"] == "self") ) {
                            $additional["link"] = $v["href"];
                            break;
                        }
                    }
                }

                $idv = $data["id"];
                $idv = str_replace('ag:youtube.com,2008:video:', '', $idv);
                $idv = substr($idv, strripos($idv, '/')+1); 

            }

            if ( ereg("feedburner", $web_url) || ($rsstype == 1) ) {
                if ( is_array($data["link"]) ) {
                    foreach ( $data["link"] AS $v ) {
                        if ( is_array($v) && isset($v["rel"]) && ($v["rel"] == "alternate") && ($v["type"] == "text/html") ) {
                            $additional["link"] = $v["href"];
                            break;
                        }
                    }
                }
            }

            if ( ereg("podcast", $web_url) || ($rsstype == 1) ) {
                $additional["audio"] = "";

                if( is_array($data["enclosure_attr"]) && isset($data["enclosure_attr"]["url"]) ) {
                    $additional["audio"] = $data["enclosure_attr"]["url"];
                }  else if ( ereg("mp3", $data["link"]) ) {
                    $additional["audio"] = $data["link"];
                } else if ( isset($data["guid"]) ) {
                    $additional["audio"] = $data["guid"];
                }
            }

            if ( $idv ) {
                $newEl = array(
                    "id" => $idv,  // Needed to make cell tappable; actually irrelevant
                    "title" => $data["title"],
                    "imageurl" => $imageurl,
                    "description" => $description,
                    "summary" => $summary,
                    "creator" => $creator,
                    "link" => $data["link"],
                    "pubDate" => $pubDate,
                    "timezone" => $data["timezone"],
                    "section" => $section
                );

                    if ( isset($additional) && is_array($additional) ) {
                    $newEl = array_merge($newEl, $additional);
            }
                }

            // See if image url is right
            if ( $newEl['imageurl'] != '' ) {
                if ( !isImage_byname($newEl['imageurl']) ) {
                    $newEl['imageurl'] = "";
                }
            }

            // Special processing....
            if ( ereg("dougturkel.com/blog/feed", $web_url) ) {
                $html_entity_decode_items = array(
                    "description", "summary", "title"
                );

                foreach ( $html_entity_decode_items AS $item) {
                    $newEl[$item] = html_entity_decode(mb_convert_encoding($newEl[$item], "UTF-8", "HTML-ENTITIES"));
                }
            }

            $feed[] = $newEl;
        }

    }

    return $feed;
}

$sql = "
    SELECT value1, view_controller, value12 FROM app_tabs
    WHERE app_id = '$app_id' AND id = '$_GET[tab_id]'
";

$res = mysql_query($sql, $conn);
$emptyFlg = false;

// -----------------------------------------------
// check if no fetched data
// -----------------------------------------------
if ( !mysql_num_rows($res) ) {
    $emptyFlg = true;
}

// ----------------------------------------------------------------------------------
// check if fetched url value is actualy empty string
// ----------------------------------------------------------------------------------
if ( !$emptyFlg ) {
    $tab_res = mysql_fetch_array($res);
    $web_url = $url = $tab_res['value1'];
    $viewc = $tab_res['view_controller'];
    $web_video_url = $tab_res['value12'];

    $rsstype = 0;

    if ( ereg("youtube.com", $url) ) {
        $viewc = "YoutubeViewController";
    }

    switch ( $viewc ) {
        case "PodcastViewController":
            $rsstype = 1;
            break;
        case "YoutubeViewController":
            $rsstype = 2;

            if ( strpos($url, "/") === false ) {
                $channel = $url;
                $web_url = $url = "http://gdata.youtube.com/feeds/api/users/".$url."/uploads";
            } else if ( ereg("youtube.com/user/([a-zA-Z0-9\-_]+)", $url, $regs) ) {
                $channel = $regs[1];
                $web_url = $url = "http://gdata.youtube.com/feeds/api/users/".$regs[1]."/uploads";
            } else if ( ereg("youtube.com/channel/([a-zA-Z0-9\-_]+)", $url, $regs) ) {
                $url_items = explode('/', $url);
                $channel = '';
                foreach ( $url_items as $key => $item ) {
                    if ( $item == 'channel' )
                        $channel = $url_items[$key + 1];
                }

                $web_url = $url = "http://gdata.youtube.com/feeds/api/users/".$channel."/uploads";
            } else {
                $web_url = $url;
            }
            break;
        default:
            $rsstype = 0;
            break;
    }
    
    // Ray added.
    $web_url = str_replace("view-source:", "", $web_url);
    $url = str_replace("view-source:", "", $url);

    if ( (strpos($url, "http://") === false) && (strpos($url, "https://") === false) ) {
        $url = "http://" . $url;
    }

    if ( $url == '' ) {
        $emptyFlg = true;
}
}

// -----------------------------------------------
// process for empty RSS
// -----------------------------------------------
if ( $emptyFlg ) {
    $feed[] = array(
        "id" => 0,
        "title" => "RSS feed not found"
    );
} else {
    $isEmptyImage = false;

    // Check if custom rss icon image uploaded
    $sql = "SELECT * FROM app_tabs WHERE app_id = '$app_id' AND view_controller = 'RSSFeedViewController' AND value3 <> '' ORDER BY last_updated DESC";
    $res = mysql_query($sql, $conn);
    $result = mysql_fetch_array($res);
    
    $rss_icon = '';
    $dir = findUploadDirectory($app_id) . "/rc_rss.$result[value3]";
    if ( !file_exists($dir) ) {
        $isEmptyImage = false;
    } else {
        $isEmptyImage = true;
    }

    $orderby = $_GET['orderby'];
    if ( !$orderby || !in_array( $orderby, array('recent', 'featured', 'popular') ) )
        $orderby = 'recent';
        
    if ( $orderby == 'featured' ) {
        $sorderby = 'rating';
    } else if ( $orderby == 'popular' ) {
        $sorderby = 'viewCount';
    } else {
        $sorderby = 'published';
    }

    $feed = array();
    if ( $rsstype == 2 ) {
        $startInd = 1;
        $pageCount = 50;

        if ( in_array($channel, array('UCXU8A0BZy--QjdvbwUs0Flg', 'UCj7f0zkCraUh9ZWmf5U7xKg', 'UCQMEsIAEe3rTHFL1dASU6Ag', 'UCb_zHwSr2K_XyLszBKfUx4g')) ) {
            $url_params = "max-results=".$pageCount."&start-index=".$startInd."&v=2";
        } else {
            $url_params = "orderby=" . $sorderby . "&max-results=".$pageCount."&start-index=".$startInd."&v=2";
        }
        $youtubeFeed = fetchRSSfromURL($url."?".$url_params, $web_url, $rsstype, $isEmptyImage);
        
        if( count($youtubeFeed) > 0 ) {
            
            $author = $youtubeFeed[0]['creator'];
            $author_result = json_decode( file_get_contents('https://gdata.youtube.com/feeds/api/users/' . $author. '?fields=media:thumbnail&alt=json'), true );
            $author_thumbnail = $author_result['entry']['media$thumbnail']['url'];
            if ( !$author_thumbnail ) {
                $author_link = split('/', str_replace('http://gdata.youtube.com/feeds/api/users/', '', $youtubeFeed[0]['link']));
                $author_id = $author_link[0];
                if ( $author_id ) {
                    $author_result = json_decode( file_get_contents('https://gdata.youtube.com/feeds/api/users/' . $author_id. '?fields=media:thumbnail&alt=json'), true );
                    $author_thumbnail = $author_result['entry']['media$thumbnail']['url'];
                }
            }
            if ( $author_thumbnail ) {
                foreach ( $youtubeFeed AS $yf ) {
                    $yf['creator_avatar'] = $author_thumbnail;
                    $yf['type'] = 'video';
                    $feed[] = $yf;
                }
            }
        }
    } else {
        $feed = fetchRSSfromURL($url, $web_url, $rsstype, $isEmptyImage);

        if ( $rsstype == 1 ) {
            if ( !empty($feed) ) {
                foreach( $feed as $key => $val ) {
                    $feed[$key]['type'] = 'audio';
                }
            }

            if ( $web_video_url ) {
                $feed2 = fetchRSSfromURL($web_video_url, $web_video_url, $rsstype, $isEmptyImage);

                if ( !empty($feed2) ) {
                    if ( !empty($feed) ) {
                        foreach( $feed as $key => $val ) {
                            $feed[$key]['type'] = 'audio';
                        }
                    }

                    foreach( $feed2 as $key => $val ) {
                        $feed2[$key]['type'] = 'video';
                    }

                    $feed = array_merge($feed, $feed2);
                }
            }
        }
    }
}

//-------------------------------------------------------------------------
// Check if returns there
//-------------------------------------------------------------------------

if( !$feed ) {
    // Initialize $feed
    $feed[] = array(
        "id" => 0,
        "title" => "RSS feed not found"
    );
}

$req_data = make_data_safe($_GET);
$bg_image = getBackgroundImageValue($conn, $app_id, $req_data, "0", "../../");
$feed[0]["background"] = $bg_image;

//-------------------------------------------------------------------------
// Assign Icon
//-------------------------------------------------------------------------
$t = get_app_tab_record($conn, $_GET["tab_id"]);

$rss_icon = '';
$dir = findUploadDirectory($app_id) . "/rc_rss_$t[id].$t[value3]";
$server = "http://appsomen.com";
if ( !file_exists($dir) ) {
    $rss_icon = $server."/uploads/icons/rss.png";
} else {
    $rss_icon = $server . '/custom_images/' . $_GET["app_code"] . '/rc_rss_' . $t["id"] . '.' . $t[value3];
}

$feed[0]["icon"] = $rss_icon;

//-------------------------------------------------------------------------
// Assign Tint Color
//-------------------------------------------------------------------------
if ( $t["value2"] == "" ) {
    $t["value2"] = "000000";
}
$feed[0]["tint"] = strtoupper($t["value2"]);

include_once("ray_model/tab_xtr.php");
$M_TabXtr = new TabXtr($conn);
$tab_xtr = $M_TabXtr->retrieve_by_tab($_GET["tab_id"]);
$feed[0]["note"] = ($tab_xtr["note"]) ? $tab_xtr["note"] : "";

if ( $_GET['first'] == '1' ) {
    $feed = array( $feed[0] );
}

//-------------------------------------------------------------------------
// make Json and print it
//-------------------------------------------------------------------------

$json = json_encode($feed);

// Ray added to remove \/ 
$issues = array('\/', '":null');
$settles = array('/','":""');

$json = str_replace($issues, $settles, $json);

// -- End

header("Content-encoding: gzip");
echo gzencode($json);

?>