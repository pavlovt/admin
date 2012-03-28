<?PHP
if(!is_callable('d')) {

if(!isset($GLOBALS['DD']))                  $GLOBALS['DD']                  = array();
if(!isset($GLOBALS['DD']['download']))        $GLOBALS['DD']['download']        = 0;
if(!isset($GLOBALS['DD']['wordwrap']))        $GLOBALS['DD']['wordwrap']        = 0;
if(!isset($GLOBALS['DD']['headers']))         $GLOBALS['DD']['headers']         = 0;
if(!isset($GLOBALS['DD']['logfile']))         $GLOBALS['DD']['logfile']         = '.#dlog.htm';
if(!isset($GLOBALS['DD']['maxlogfilesize']))  $GLOBALS['DD']['maxlogfilesize']  = 500000;
if(!isset($GLOBALS['DD']['maxstringforhex'])) $GLOBALS['DD']['maxstringforhex'] = 50;
if(!isset($GLOBALS['DD']['watchcookie']))     $GLOBALS['DD']['watchcookie']     = 0;
if(!isset($GLOBALS['DD']['init']))            $GLOBALS['DD']['init']            = 0;

#########################################################################
#########################################################################
#########################################################################
/*
 * dd - DataDumper - Dump any resource with syntax highlighting, 
 *      indenting and variable type information to the screen in a very intuitive format
 *
 * based on Dumpr and on dBug
 *
 * Licensed under the terms of the GNU Lesser General Public License:
 *      http://www.opensource.org/licenses/lgpl-license.php
 *
 * Author    Emile Schenk
 *           https://sourceforge.net/projects/datadumper
 * License   LGPL
 * Modified  November 2010
 * Revision  3.92
 * 
 * Changes 
 *     Revision 2.0
 *         - Initial release taken from dumpr version 1.8
 *         - added: file and line number of dumpr-call
 *         - added: name of variable displayed
 *         - added: logging into a file instead of screen (parameter 2, $DD['logfile'] needs to be global defined)
 *     Revision 2.1
 *         - added: when no parameter is given, $_SESSION, $_GET, $_POST are displayed
 *         - added: function dde(...), exits after displaying variables
 *     Revision 2.2
 *         - changed: function ddf(...) is now used for writing into the file $DD['logfile']
 *     Revision 2.3
 *         - changed: $DD['logfile'] has a default: ddlog.htm (in the current directory)
 *         - new parameter $DD['download'] if=0 then no downloadlink is shown
 *         - new parameter $DD['wordwrap'] if=0 then no wordwraplink is shown
 *         - new parameter $DD[fileline] if=0 then no file and line-info is shown
 *     Revision 2.4
 *         - changed: Productlink to sourceforge
 *     Revision 2.5
 *         - changed: Resolved error in productlink 
 *     Revision 2.6
 *         - new parameter $DD[off] if=1 then dd is off (for easy switching off debugging)
 *     Revision 3.0
 *         - completely new revision also based on dBug
 *         - strings up to $GLOBALS['DD']['maxstringforhex'] characters are also shown in HEX format
 *         - functionnames are even shorter now: 
 *           - d for display variable
 *           - de for display and exit
 *           - df for display in file
 *           - dc display variable only when cookie is set: $_COOKIE['ddphp'] == 1
 *     Revision 3.1
 *         - DD-variables can be defined before including dd.php
 *     Revision 3.2
 *         - display is always on top: position:relative and z-index:999999
 *     Revision 3.3
 *         - added a cookie configuration variable: $GLOBALS['DD']['watchcookie']
 *           this variable can be set to 1 with dcookie()
 *           when it is set to 1 then dd outputs only if $_COOKIE['ddphp']==1
 *           this cookie can be set in javascript with the following bookmarklet, expires in 1 hour
 *           javascript:var expdate=new Date();expdate.setTime(expdate.getTime() + (1000*60*60));expdatestr=expdate.toGMTString();expdatestr=expdatestr.replace('UTC', '');s= "ddphp=1" + "; expires=" + expdatestr;document.cookie=s;void(0);
 *           expires in 10 years:
 *           javascript:var expdate=new Date();expdate.setTime(expdate.getTime() + (1000*60*60*24*3650));expdatestr=expdate.toGMTString();expdatestr=expdatestr.replace('UTC', '');s= "ddphp=1" + "; expires=" + expdatestr;document.cookie=s;void(0);
 *     Revision 3.4
 *         - Function df(): When the logfile is larger than 500.000 bytes a backup is made with a timestamp in the filename
 *           and a new file is generated for the log. The size can be set in $GLOBALS['DD']['maxlogfilesize'].
 *         - New messages are appended to the logfile (in stead or prepended in earlier versions). This is more logical and faster.
 *     Revision 3.41
 *         - Error correction with creation of backup files
 *     Revision 3.5
 *         - dd.php can be included twice without any error
 *         - Added check if analysed variable isset
 *     Revision 3.6
 *         - improved output so that it is (a bit) more readable when output on console (e.g. in Firebug)
 *         - default logfilename is .#dlog.htm . This file is not uploaded by TortoiseSVN by default.
 *     Revision 3.7
 *         - Object PDO cannot be serialized
 *         - Display [NULL] as array-value
 *     Revision 3.8
 *         - No error when variable cannot be serialized
 *         - Output of CSS only once even if d() is called several times
 *     Revision 3.9
 *         - error corrected, get_class() could have array as parameter
 *	   Revision 3.91
 *		   - corrected everything that caused php to print warnings when using the d() function
 *		   - With thanks to Patrick Neschkudla
 *	   Revision 3.92
 *		   - corrected boolean in object
 *	   Revision 3.93
 *		   - corrected for unserializable objects
 *	   Revision 3.94
 *		   - $this->maxarraylevel corrected
 */
#########################################################################
#########################################################################
#########################################################################

function dd() {
	d('Please use the new functionname: d()');
}

function d() {
	if($GLOBALS['DD']['watchcookie']!=1 || $_COOKIE['ddphp']==1) {
		new dBug( func_get_args() );
	}
}

function dc() {
	if($_COOKIE['ddphp'] == 1) {
		new dBug( func_get_args() );
	}
}

function dcookie() {
	$GLOBALS['DD']['watchcookie'] = 1;
}

function de() {
	if($GLOBALS['DD']['watchcookie']!=1 || $_COOKIE['ddphp']==1) {
		new dBug( func_get_args() );
		exit;
	}
}

function df() {
	if($GLOBALS['DD']['watchcookie']!=1 || $_COOKIE['ddphp']==1) {
		$data=func_get_args();
		$data['file']=1;
		new dBug($data);
	}
}

#############################################
class dBug {
	var $xmlDepth=array();
	var $xmlCData;
	var $xmlSData;
	var $xmlDData;
	var $xmlCount=0;
	var $xmlAttrib;
	var $xmlName;
	var $arrType=array("array","object","resource");
	var $arraydim = 0;
	var $bInitialized = false;
	var $arrHistory = array();
	var $logfile='_dlog.htm';

	//constructor
	function dBug($var) {
		if(isset($var['file'])){
			$this->log2file=$var['file'];
		}
		$this->getVariableName();
		if(isset($GLOBALS['DD']['logfile'])) $this->logfile = $GLOBALS['DD']['logfile'];
		unset($var['file']);
		if(count($var)==0) $datax = array($this->varname.'$_SESSION' => $_SESSION, $this->varname.'$_POST' => $_POST);
		else $datax = array($this->varname => $var[0]);
	
/*
		if($this->log2file ==1) {
			if(file_exists($this->logfile)) {
				$stat = stat($this->logfile);
				if($stat[size] < $DD['maxlogfilesize']) $cont = file_get_contents($this->logfile);
				$cont_a=explode('#~#~#~', $cont);
				$now=mktime();
				$this->logdata = $cont_a[0];
				for($i=1; $i<count($cont_a); $i+=2) {
					if($cont_a[$i] > ($now-7200)) {
						$this->logdata .= "#~#~#~$cont_a[$i]#~#~#~\n".$cont_a[$i+1]."\n";
					}
				}
			}
			else {
				$this->logdata = $this->initJSandCSS();
			}
		}
*/
		
		foreach($datax as $i => $d) {
			$this->bInitialized = false;
			$this->varname = $i;
			$this->dBug2($d);
		}
		
		if(isset($this->log2file)){
			if($this->log2file ==1) {
				if(file_exists($this->logfile)) {
					$logfilesize = filesize($this->logfile);
					if($logfilesize > 500000) {
						$dd = date("ymd_Hi");
						$tmp = explode('.', $this->logfile);
						$tmp[ count($tmp)-2 ] .= "_$dd";
						$bakname = implode('.', $tmp);
						$ret = rename($this->logfile, $bakname);
						$this->out = $this->initJSandCSS() . "\n" . $this->out;
					}
				}
				else {
					$this->out = $this->initJSandCSS() . "\n" . $this->out;
				}
				
				
				if(error_log($this->out, 3, $this->logfile) === false) {
					echo "<p>Logfile cannot be written: {$this->logfile}</p>";
				}
			}
			else {
			//include js and css scripts
				if($GLOBALS['DD']['init'] == 0) {
					$GLOBALS['DD']['init'] = 1;
					$this->out = $this->initJSandCSS() . $this->out;
				}
				echo $this->out;
			}
		}else{
			//include js and css scripts
			if($GLOBALS['DD']['init'] == 0) {
				$GLOBALS['DD']['init'] = 1;
				$this->out = $this->initJSandCSS() . $this->out;
			}
			echo $this->out;
		}
	}

###################################
	function dBug2($var) {
		$arrAccept=array("array","object","xml"); //array of variable types that can be "forced"
		
		if(isset($forceType)){
			if(in_array($forceType,$arrAccept))
				$this->{"varIs".ucfirst($forceType)}($var);
			else
				$this->checkType($var);
		}else{
			$this->checkType($var);
		}
	}

###################################
	function ddserialize($var) {
		try {
			$var_ser = serialize($var);
		}
		catch(Exception $e) {
			if(is_object($var)) {
				$class = get_class($var);
			}
			$var_ser = "Cannot serialize variable of class $class (dummy number " . rand() . ')';
		}
		return $var_ser;
	}

###################################
	//get variable name
	function getVariableName() {
		$arrBacktrace = debug_backtrace();

		//possible 'included' functions
		$arrInclude = array("include","include_once","require","require_once");
		
		//check for any included/required files. if found, get array of the last included file (they contain the right line numbers)
		for($i=count($arrBacktrace)-1; $i>=0; $i--) {
			$arrCurrent = $arrBacktrace[$i];
			if(array_key_exists("function", $arrCurrent) && 
				(in_array($arrCurrent["function"], $arrInclude) || ($arrCurrent["function"] != "d" && $arrCurrent["function"] != "dc" && $arrCurrent["function"] != "de" && $arrCurrent["function"] != "df")))
				continue;

			$arrFile = $arrCurrent;
			
			break;
		}
		
		$arrLines = file($arrFile["file"]);
		$code = $arrLines[($arrFile["line"]-1)];
		//find call to dBug class
		preg_match('/\bde{0,1}f{0,1}c{0,1}\s*\(\s*(.+)\s*\);/i', $code, $arrMatches);
#dd($arrBacktrace);		
		$this->varname = $arrMatches[1];
		if(isset($this->log2file)){
			if($this->log2file ==1) {
					$url_time = (isset($_SERVER[HTTPS])) ? 'https' : 'http';
					$url_time .= "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI] &nbsp;- &nbsp;".date("d.m.y H:i:s").'<br>';
					$this->varname = "<span style='font-weight:normal;'>$url_time</span>{$this->varname}";
			}
		}
		$this->file = str_replace('\\', '/', $arrFile['file']);
		$this->line = $arrFile['line'];
	}
	
	//create the main table header
	function makeTableHeader($type,$header,$colspan=2) {
		if(!$this->bInitialized) {
			$header = "{$this->varname} ($header), <span style='font-weight:normal;'>{$this->file} - line {$this->line}</span>";
			$this->bInitialized = true;
		}
		else {
			$header = "{$this->name} ($header)";
		}
		$this->out .= "\n\n<table cellspacing=1 cellpadding=1 class=\"dBug_".$type."\"><tr>\n<td class=\"dBug_".$type."Header\" colspan=".$colspan.">".$header."</td></tr>";
	}
	
	//create the table row header
	function makeTDHeader($type,$header) {
		$this->out .= "<tr>\n<td valign=\"top\" class=\"dBug_".$type."Key\">".$header."</td><td>";
	}
	
	//close table row
	function closeTDRow() {
		return "</td></tr>\n";
	}
	
	//error
	function  error($type) {
		$error="Error: Variable cannot be a";
		// this just checks if the type starts with a vowel or "x" and displays either "a" or "an"
		if(in_array(substr($type,0,1),array("a","e","i","o","u","x")))
			$error.="n";
		return ($error." ".$type." type");
	}

	//check variable type
	function checkType($var) {
		switch(gettype($var)) {
			case "resource":
				$this->varIsResource($var);
				break;
			case "object":
				$this->varIsObject($var);
				break;
			case "array":
				$this->arraylevel++;
				if(!isset($this->maxarraylevel)) {
					$this->maxarraylevel = 0;
				}
				$this->maxarraylevel = max($this->arraylevel, $this->maxarraylevel);
				$this->varIsArray($var);
				$this->arraylevel--;
				if($this->arraylevel == 0) $this->maxarraylevel = 0;
				break;
			case "boolean":
				$this->varIsArray($var);
				break;
			default:
				$this->varIsArray($var);
				break;
				$var=($var==="") ? "[empty string]" : $var;
				$this->out .= "\n\n<table cellspacing=0><tr>\n<td>".$var."</td>\n</tr>\n</table>\n\n";
				break;
		}
	}
	
	//if variable is a boolean type
	function varIsBoolean($var) {
		$var=($var==1) ? "[TRUE]" : "[FALSE]";
		$this->out .= $var;
	}
			
	//if variable is an array type
	function varIsArray($var) {
		$var_orig = $var;
		$var_ser = $this->ddserialize($var);
		array_push($this->arrHistory, $var_ser);
		$this->arraydim = max($this->arraydim, count($this->arrHistory));
		
		if(is_array($var)) $this->makeTableHeader("array", '^°^°^°'.$this->arraylevel.'^°^°^°');
		elseif(is_bool($var)) {
			$this->makeTableHeader("object","bool");
			$var=($var==1) ? "TRUE" : "FALSE";
		}
		elseif(is_double($var)) {
			$this->makeTableHeader("object","double");
		}
		elseif(is_int($var)) {
			$this->makeTableHeader("object","integer");
		}
		elseif(is_null($var)) {
			$this->makeTableHeader("object","NULL");
		}
		else {
			$length = strlen($var);
			$this->makeTableHeader("object","string [$length]");
		}
		if(is_array($var)) {
			foreach($var as $key=>$value) {
				$this->name=$key;
				$this->makeTDHeader("array",$key);
				
				//check for recursion
				if(is_array($value)) {
					$var_ser = $this->ddserialize($value);
					if(in_array($var_ser, $this->arrHistory, TRUE))
						$value = "*RECURSION*";
				}
				
				if(in_array(gettype($value),$this->arrType)) {
					$this->checkType($value);
				}
				else {
					if(is_bool($value)) {
						$value=($value==1) ? "[TRUE]" : "[FALSE]";
					}
					if(is_null($value)) {
						$value = '[NULL]';
					}
					$value=(($value)==="") ? "[empty string]" : $value;
					if(strpos($value, 'http')===0) $value = "<a href='$value'>$value</a>";
					else $value = nl2br(htmlspecialchars($value));
					$this->out .= $value;
				}
				$this->out .= $this->closeTDRow();
			} # end foreach
			$arraydim = $this->maxarraylevel - $this->arraylevel + 1;
			$this->out = str_replace('^°^°^°'.$this->arraylevel.'^°^°^°', 'array, '.$arraydim.'-dim', $this->out);
		}
		else {
			if(is_null($var)) {
				$var = '[NULL]';
			}
			$this->out .= "<tr>\n<td>".nl2br(htmlspecialchars($var)).$this->closeTDRow();
			if(is_string($var_orig) && strlen($var_orig)<=$GLOBALS['DD']['maxstringforhex']) {
				$hex = "";
				for($i=0;$i<strlen($var_orig);$i++) {
					$hex .= sprintf("%02X ",ord($var_orig{$i}));
				}
				$this->out .= "<tr>\n<td>HEX: $hex" . $this->closeTDRow();
			}
		}
		array_pop($this->arrHistory);
		$this->out .= "</table>\n\n";
	}
	
	//if variable is an object type
	function varIsObject($var) {
		$class = get_class($var);
		$var_ser = $this->ddserialize($var);
		array_push($this->arrHistory, $var_ser);
		$this->makeTableHeader("object", "object of class $class");
		
		if(is_object($var)) {
			$arrObjVars=get_object_vars($var);
			foreach($arrObjVars as $key=>$value) {

				$this->name=$key;
				$this->makeTDHeader("object",$key);
				
				//check for recursion
				if(is_object($value)||is_array($value)) {
					$var_ser = $this->ddserialize($value);
					if(in_array($var_ser, $this->arrHistory, TRUE)) {
						$value = (is_object($value)) ? "*RECURSION* -> $".get_class($value) : "*RECURSION*";

					}
				}
				if(in_array(gettype($value),$this->arrType)) {
					$this->checkType($value);
				}
				else {
					if(is_bool($value)) {
						$value=($value==1) ? "[TRUE]" : "[FALSE]";
					}
					if(is_null($value)) {
						$value = '[NULL]';
					}
					$value=(($value)==="") ? "[empty string]" : $value;
					if(strpos($value, 'http')===0) $value = "<a href='$value'>$value</a>";
					else $value = nl2br(htmlspecialchars($value));
					$this->out .= $value;
				}
				$this->out .= $this->closeTDRow();
			}
#			$arrObjMethods=get_class_methods(get_class($var));
#			foreach($arrObjMethods as $key=>$value) {
#				$this->makeTDHeader("object",$value);
#				$this->out .= "[function]".$this->closeTDRow();
#			}
		}
		else $this->out .= "<tr>\n<td>".$this->error("object").$this->closeTDRow();
		array_pop($this->arrHistory);
		$this->out .= "</table>\n\n";
	}

	//if variable is a resource type
	function varIsResource($var) {
		$this->makeTableHeader("resourceC","resource",1);
		$this->out .= "<tr>\n<td>\n";
		switch(get_resource_type($var)) {
			case "fbsql result":
			case "mssql result":
			case "msql query":
			case "pgsql result":
			case "sybase-db result":
			case "sybase-ct result":
			case "mysql result":
				$tmp = explode(" ",get_resource_type($var));
				$db=current($tmp);
				$this->varIsDBResource($var,$db);
				break;
			case "gd":
				$this->varIsGDResource($var);
				break;
			case "xml":
				$this->varIsXmlResource($var);
				break;
			default:
				$this->out .= get_resource_type($var).$this->closeTDRow();
				break;
		}
		$this->out .= $this->closeTDRow()."</table>\n\n";
	}

	//if variable is a database resource type
	function varIsDBResource($var,$db="mysql") {
		if($db == "pgsql")
			$db = "pg";
		if($db == "sybase-db" || $db == "sybase-ct")
			$db = "sybase";
		$arrFields = array("name","type","flags");	
		$numrows=call_user_func($db."_num_rows",$var);
		$numfields=call_user_func($db."_num_fields",$var);
		$this->makeTableHeader("resource",$db." result",$numfields+1);
		$this->out .= "<tr><td class=\"dBug_resourceKey\">&nbsp;</td>";
		for($i=0;$i<$numfields;$i++) {
			$field_header = "";
			for($j=0; $j<count($arrFields); $j++) {
				$db_func = $db."_field_".$arrFields[$j];
				if(function_exists($db_func)) {
					$fheader = call_user_func($db_func, $var, $i). " ";
					if($j==0)
						$field_name = $fheader;
					else
						$field_header .= $fheader;
				}
			}
			$field[$i]=call_user_func($db."_fetch_field",$var,$i);
			$this->out .= "\n<td class=\"dBug_resourceKey\" title=\"".$field_header."\">".$field_name."</td>";
		}
		$this->out .= "</tr>";
		for($i=0;$i<$numrows;$i++) {
			$row=call_user_func($db."_fetch_array",$var,constant(strtoupper($db)."_ASSOC"));
			$this->out .= "<tr>\n";
			$this->out .= "\n<td class=\"dBug_resourceKey\">".($i+1)."</td>"; 
			for($k=0;$k<$numfields;$k++) {
				$tempField=$field[$k]->name;
				$fieldrow=$row[($field[$k]->name)];
				$fieldrow=($fieldrow==="") ? "[empty string]" : $fieldrow;
				$this->out .= "\n<td>".$fieldrow."</td>\n";
			}
			$this->out .= "</tr>\n";
		}
		$this->out .= "</table>\n\n";
		if($numrows>0)
			call_user_func($db."_data_seek",$var,0);
	}
	
	//if variable is an image/gd resource type
	function varIsGDResource($var) {
		$this->makeTableHeader("resource","gd",2);
		$this->makeTDHeader("resource","Width");
		$this->out .= imagesx($var).$this->closeTDRow();
		$this->makeTDHeader("resource","Height");
		$this->out .= imagesy($var).$this->closeTDRow();
		$this->makeTDHeader("resource","Colors");
		$this->out .= imagecolorstotal($var).$this->closeTDRow();
		$this->out .= "</table>\n\n";
	}
	
	//if variable is an xml type
	function varIsXml($var) {
		$this->varIsXmlResource($var);
	}
	
	//if variable is an xml resource type
	function varIsXmlResource($var) {
		$xml_parser=xml_parser_create();
		xml_parser_set_option($xml_parser,XML_OPTION_CASE_FOLDING,0); 
		xml_set_element_handler($xml_parser,array(&$this,"xmlStartElement"),array(&$this,"xmlEndElement")); 
		xml_set_character_data_handler($xml_parser,array(&$this,"xmlCharacterData"));
		xml_set_default_handler($xml_parser,array(&$this,"xmlDefaultHandler")); 
		
		$this->makeTableHeader("xml","xml document",2);
		$this->makeTDHeader("xml","xmlRoot");
		
		//attempt to open xml file
		$bFile=(!($fp=@fopen($var,"r"))) ? false : true;
		
		//read xml file
		if($bFile) {
			while($data=str_replace("\n","",fread($fp,4096)))
				$this->xmlParse($xml_parser,$data,feof($fp));
		}
		//if xml is not a file, attempt to read it as a string
		else {
			if(!is_string($var)) {
				$this->out .= $this->error("xml").$this->closeTDRow()."</table>\n\n";
				return;
			}
			$data=$var;
			$this->xmlParse($xml_parser,$data,1);
		}
		
		$this->out .= $this->closeTDRow()."</table>\n\n";
		
	}
	
	//parse xml
	function xmlParse($xml_parser,$data,$bFinal) {
		if (!xml_parse($xml_parser,$data,$bFinal)) { 
				   die(sprintf("XML error: %s at line %d\n", 
							   xml_error_string(xml_get_error_code($xml_parser)), 
							   xml_get_current_line_number($xml_parser)));
		}
	}
	
	//xml: inititiated when a start tag is encountered
	function xmlStartElement($parser,$name,$attribs) {
		$this->xmlAttrib[$this->xmlCount]=$attribs;
		$this->xmlName[$this->xmlCount]=$name;
		$this->xmlSData[$this->xmlCount]='$this->makeTableHeader("xml","xml element",2);';
		$this->xmlSData[$this->xmlCount].='$this->makeTDHeader("xml","xmlName");';
		$this->xmlSData[$this->xmlCount].='$this->out .= "<strong>'.$this->xmlName[$this->xmlCount].'</strong>".$this->closeTDRow();';
		$this->xmlSData[$this->xmlCount].='$this->makeTDHeader("xml","xmlAttributes");';
		if(count($attribs)>0)
			$this->xmlSData[$this->xmlCount].='$this->varIsArray($this->xmlAttrib['.$this->xmlCount.']);';
		else
			$this->xmlSData[$this->xmlCount].='$this->out .= "&nbsp;";';
		$this->xmlSData[$this->xmlCount].='$this->out .= $this->closeTDRow();';
		$this->xmlCount++;
	} 
	
	//xml: initiated when an end tag is encountered
	function xmlEndElement($parser,$name) {
		for($i=0;$i<$this->xmlCount;$i++) {
			eval($this->xmlSData[$i]);
			$this->makeTDHeader("xml","xmlText");
			$this->out .= (!empty($this->xmlCData[$i])) ? $this->xmlCData[$i] : "&nbsp;";
			$this->out .= $this->closeTDRow();
			$this->makeTDHeader("xml","xmlComment");
			$this->out .= (!empty($this->xmlDData[$i])) ? $this->xmlDData[$i] : "&nbsp;";
			$this->out .= $this->closeTDRow();
			$this->makeTDHeader("xml","xmlChildren");
			unset($this->xmlCData[$i],$this->xmlDData[$i]);
		}
		$this->out .= $this->closeTDRow();
		$this->out .= "</table>\n\n";
		$this->xmlCount=0;
	} 
	
	//xml: initiated when text between tags is encountered
	function xmlCharacterData($parser,$data) {
		$count=$this->xmlCount-1;
		if(!empty($this->xmlCData[$count]))
			$this->xmlCData[$count].=$data;
		else
			$this->xmlCData[$count]=$data;
	} 
	
	//xml: initiated when a comment or other miscellaneous texts is encountered
	function xmlDefaultHandler($parser,$data) {
		//strip '<!--' and '-->' off comments
		$data=str_replace(array("&lt;!--","--&gt;"),"",htmlspecialchars($data));
		$count=$this->xmlCount-1;
		if(!empty($this->xmlDData[$count]))
			$this->xmlDData[$count].=$data;
		else
			$this->xmlDData[$count]=$data;
	}

	function initJSandCSS() {
		$out = 
<<<SCRIPTS
<style type="text/css">
table.dBug_array,table.dBug_object,table.dBug_resource,table.dBug_resourceC,table.dBug_xml {
	font-family:Verdana, Arial, Helvetica, sans-serif; color:#000000; font-size:8pt; -ms-filter:"progid:DXImageTransform.Microsoft.Alpha(Opacity=88)"; filter:alpha(opacity=88); opacity:0.88; 
}
table.dBug_array td,table.dBug_object td,table.dBug_resource td,table.dBug_resourceC td,table.dBug_xml td {
	font-family:Verdana, Arial, Helvetica, sans-serif; color:#000000; font-size:7pt;
}
.dBug_arrayHeader,
.dBug_objectHeader,
.dBug_resourceHeader,
.dBug_resourceCHeader,
.dBug_xmlHeader 
	{ font-weight:bold; color:#FFFFFF; cursor:pointer; }
.dBug_arrayKey,
.dBug_objectKey,
.dBug_xmlKey 
	{ cursor:pointer; }
/* array */
table.dBug_array { background-color:#00A000; margin-top:8px; position:relative; z-index:99999999;}
table.dBug_array td { background-color:#FFFFFF;  padding-left:3px; font-size:8pt; }
table.dBug_array td.dBug_arrayHeader { background-color:#90FF90; }
table.dBug_array td.dBug_arrayKey { background-color:#CCFFCC; text-align:right; padding-right:5px; }
/* object */
table.dBug_object { background-color:#4040FF; margin-top:8px; position:relative; z-index:99999999;}
table.dBug_object td { background-color:#FFFFFF;  font-size:8pt; }
table.dBug_object td.dBug_objectHeader { background-color:#C0C0FF; }
table.dBug_object td.dBug_objectKey { background-color:#CCDDFF; text-align:right; padding-right:5px; }
/* resource */
table.dBug_resourceC { background-color:#884488; margin-top:8px; position:relative; z-index:99999999;}
table.dBug_resourceC td { background-color:#FFFFFF;  font-size:8pt; }
table.dBug_resourceC td.dBug_resourceCHeader { background-color:#AA66AA; }
table.dBug_resourceC td.dBug_resourceCKey { background-color:#FFDDFF; text-align:right; padding-right:5px; }
/* resource */
table.dBug_resource { background-color:#884488; margin-top:8px; position:relative; z-index:99999999;}
table.dBug_resource td { background-color:#FFFFFF;  font-size:8pt; }
table.dBug_resource td.dBug_resourceHeader { background-color:#AA66AA; }
table.dBug_resource td.dBug_resourceKey { background-color:#FFDDFF; text-align:right; padding-right:5px; }
/* xml */
table.dBug_xml { background-color:#888888; position:relative; z-index:99999999;}
table.dBug_xml td { background-color:#FFFFFF;  font-size:8pt; }
table.dBug_xml td.dBug_xmlHeader { background-color:#AAAAAA; }
table.dBug_xml td.dBug_xmlKey { background-color:#DDDDDD; text-align:right; padding-right:5px; }
</style>


SCRIPTS;
		$out = str_replace("\n", ' ', $out);
		$out = str_replace("\r", ' ', $out);
		return $out;
	}

}

} # end if(!is_callable('dd'))
?>