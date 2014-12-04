<? 

$PASS_PARAM_NAMES = array(
	"app_code", "tab_id", "loc_id", "orderstr", "fwwtk", "fww",
);

$PASS_PARAMS = "";
$POST_PARAMS = "";
foreach($PASS_PARAM_NAMES AS $v) {
	if($data[$v] != "") {
		if($PASS_PARAMS != "") $PASS_PARAMS .= "&";
		$PASS_PARAMS .= $v."=".urlencode($data[$v]);
		$POST_PARAMS .= '<input type="hidden" name="'.$v.'" value="'.$data[$v].'" />';
	} else if($_SESSION[$v]) {
		if($PASS_PARAMS != "") $PASS_PARAMS .= "&";
		$PASS_PARAMS .= $v."=".urlencode($_SESSION[$v]);
		$POST_PARAMS .= '<input type="hidden" name="'.$v.'" value="'.$_SESSION[$v].'" />';
	}
}

?>