<?php
$v = $_GET["v"];
echo <<< EOF
<meta name="viewport" content="width=320; user-scalable=no">
<div align=center style="font-family: verdana, sans-serif;">
Tap the thumbnail <br>to play the movie.
<br>
<br>
<object width="240" height="128">
<param name="movie" value="http://www.youtube.com/v/$v?fs=1&amp;hl=en_US">
</param>
<param name="allowFullScreen" value="true"></param>
<param name="allowscriptaccess" value="always"></param>
<embed src="http://www.youtube.com/v/$v?fs=1&amp;hl=en_US" type="application/x-shockwave-flash" allowscriptaccess="always" allowfullscreen="true" width="240" height="128"></embed>
</object>
EOF;
