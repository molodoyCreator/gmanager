<?php
// кодировка UTF-8
/**
 * 
 * This software is distributed under the GNU LGPL v3.0 license.
 * @author Gemorroj
 * @copyright 2008-2009 http://wapinet.ru
 * @license http://www.gnu.org/licenses/lgpl-3.0.txt
 * @link http://wapinet.ru/gmanager/
 * @version 0.7
 * 
 * PHP version >= 5.2.1
 * 
 */


require 'config.php';

$ms = microtime(true);

if ($auth) {
    auth($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']);
}




function auth($user, $pass)
{
        if ($user != $GLOBALS['user_name'] || $pass != $GLOBALS['user_pass']) {
            header('WWW-Authenticate: Basic realm="Authentification"');
            header('HTTP/1.0 401 Unauthorized');
            header("Content-type: text/html; charset=utf-8");
            exit('<html><head><title>Error</title></head><body><p style="color:red;font-size:24pt;text-align:center">Unauthorized</p></body></html>');
        }
}


function send_header($u = '')
{
    /*
    if(substr_count($u, 'MSIE')){
    	header('Content-type: text/html; charset=UTF-8');
	}
    else{
    	header('Content-type: application/xhtml+xml; charset=UTF-8');
	}
    */
    header('Content-type: text/html; charset=UTF-8');
    header('Cache-control: no-cache');
    
    // кол-во файлов на странице
    $ip = isset($_POST['limit']);
    $ig = isset($_GET['limit']);
	$GLOBALS['limit'] = abs($ip ? $_POST['limit'] : ($ig ? $_GET['limit'] : (isset($_COOKIE['limit']) ? $_COOKIE['limit'] : $GLOBALS['limit'])));

	if($ip || $ig){
		setcookie('limit', $GLOBALS['limit'], 2592000 + time());
	}
}


function c($query = '', $c = '')
{

    if (!$query) {
        return '.';
    } else {
        if ($c) {
            $current = str_replace('\\', '/', trim(rawurldecode($c)));

            if ($GLOBALS['mode']->is_dir($current) || $GLOBALS['mode']->is_link($current)) {
            	$l = strrev($current);
                if ($l[0] != '/') {
                    $current .= '/';
                }
            }
            return $current;
        } else {
            $query = str_replace('\\', '/', trim(rawurldecode($query)));
            if ($GLOBALS['mode']->is_dir($query) || $GLOBALS['mode']->is_link($query)) {
            	$l = strrev($query);
                if ($l[0] != '/') {
                    $query .= '/';
                }
            }
            return $query;
        }
    }
}


function this($current = '')
{	
	if($GLOBALS['class'] != 'ftp'){
    $realpath = realpath($current);
    $realpath = $realpath ? $realpath : $current;
	}
	else{
		$realpath = $current;
	}
    $chmod = look_chmod($current);
    $chmod = $chmod ? $chmod : (isset($_POST['chmod'][0]) ? htmlspecialchars($_POST['chmod'][0], ENT_NOQUOTES) : (isset($_POST['chmod']) ? htmlspecialchars($_POST['chmod'], ENT_NOQUOTES) : 0));

    $d = dirname(str_replace('\\', '/', $realpath));
    $archive = is_archive(get_type($current));
    
    if ($GLOBALS['mode']->is_dir($current) || $GLOBALS['mode']->is_link($current)) {
        if ($current == '.') {
            return '<div class="border">' . $GLOBALS['lng']['dir'] . ' <strong><a href="index.php">' . htmlspecialchars($GLOBALS['mode']->getcwd(), ENT_NOQUOTES) . '</a></strong> (' . look_chmod($GLOBALS['mode']->getcwd()) . ')<br/></div>';
        } else {
            return '<div class="border">' . $GLOBALS['lng']['back'] . ' <a href="index.php?' . str_replace('%2F', '/', rawurlencode($d)) . '">' . $d . '</a> (' . look_chmod($d) . ')<br/></div><div class="border">' . $GLOBALS['lng']['dir'] . ' <strong><a href="index.php?' . str_replace('%2F', '/', rawurlencode($current)) . '">' . htmlspecialchars(str_replace('\\', '/', $realpath), ENT_NOQUOTES) . '</a></strong> (' . $chmod . ')<br/></div>';
        }
    } elseif ($GLOBALS['mode']->is_file($current) && $archive) {
        $up = dirname($d);
        return '<div class="border">' . $GLOBALS['lng']['back'] . ' <a href="index.php?' . str_replace('%2F', '/', rawurlencode($up)) . '">' . htmlspecialchars($up, ENT_NOQUOTES) . '</a> (' . look_chmod($up) . ')<br/></div><div class="border">' . $GLOBALS['lng']['dir'] . ' <strong><a href="index.php?' . str_replace('%2F', '/', rawurlencode($d)) . '">' . $d . '</a></strong> (' . look_chmod($d) . ')<br/></div><div class="border">' . $GLOBALS['lng']['file'] . ' <strong><a href="index.php?' . str_replace('%2F', '/', rawurlencode($current)) . '">' . htmlspecialchars(str_replace('\\', '/', $realpath), ENT_NOQUOTES) . '</a></strong> (' . $chmod . ')<br/></div>';
	} else {
        $up = dirname($d);
        return '<div class="border">' . $GLOBALS['lng']['back'] . ' <a href="index.php?' . str_replace('%2F', '/', rawurlencode($up)) . '">' . htmlspecialchars($up, ENT_NOQUOTES) . '</a> (' . look_chmod($up) . ')<br/></div><div class="border">' . $GLOBALS['lng']['dir'] . ' <strong><a href="index.php?' . str_replace('%2F', '/', rawurlencode($d)) . '">' . $d . '</a></strong> (' . look_chmod($d) . ')<br/></div><div class="border">' . $GLOBALS['lng']['file'] . ' <strong><a href="edit.php?' . str_replace('%2F', '/', rawurlencode($current)) . '">' . htmlspecialchars(str_replace('\\', '/', $realpath), ENT_NOQUOTES) . '</a></strong> (' . $chmod . ')<br/></div>';
    }
}


function static_name($current = '', $dest = '')
{
	$substr = 'iconv_substr';
    if(!$len = iconv_strlen($current)){
    	$len = strlen($current);
    	$substr = 'substr';
   	}

    if ($substr($dest, 0, $len) == $current) {
        $static = $substr($dest, $len);

        if (strpos($static, '/')) {
            $static = strtok($static, '/');
        }
    }
	else {
        return '';
    }
    return $static;
}


function look($current = '', $itype = '', $down = '')
{
	if(!$GLOBALS['mode']->is_dir($current)){
		return;
	}
	
	$out = $target = $add = '';
    $page = $page1 = $page2 = array();
    $i = 0;

    if ($GLOBALS['target']) {
        $target = ' target="_blank"';
    }

    if ($GLOBALS['ia']) {
        $add = '&amp;go=1&amp;add_archive=' . str_replace('%2F', '/', rawurlencode($_GET['add_archive']));
    }


    if ($itype == 'time') {
		$out = '&amp;time';
    }
    else if ($itype == 'type') {
		$out = '&amp;type';
   	}
   	else if ($itype == 'size') {
		$out = '&amp;size';
	}
	else if ($itype == 'chmod') {
		$out = '&amp;chmod';
	}

	$out .= $down ? '&amp;down' : '&amp;up';

	$key = $time = $type = $isize = $chmod = $name = '';

		if ($itype == 'time') {
            $key = & $time;
        }
        else if ($itype == 'type') {
        	$key = & $type;
       	}
       	else if ($itype == 'size') {
       		$key = & $isize;
		}
       	else if ($itype == 'chmod') {
       		$key = & $chmod;
		}
		else {
            $key = & $name;
        }


    foreach($GLOBALS['mode']->iterator($current) as $file){

    	$i++;
    	$pname = $pdown = $ptype = $psize = $pchange = $pdel = $pchmod = $pdate = $name = $size = $isize = $chmod = '';

/*
		if(substr($file, -1) == '/'){
			$file = iconv_substr($file, 0, iconv_strlen($file)-1);	
		}
*/


        if ($current != '.') {
            $file = $current . $file;
        }

		$basename = basename($file);

		$r_file = str_replace('%2F', '/', rawurlencode($file));

		$type = htmlspecialchars(get_type($file), ENT_NOQUOTES);
		$archive = is_archive($type);

		if($fdir = ($GLOBALS['mode']->is_dir($file) || $GLOBALS['mode']->is_link($file))){

			if($GLOBALS['index']['name']){
			//
			if ($GLOBALS['realname'] == 1) {
        		$realpath = realpath($file);
        		$name = $realpath ? str_replace('\\', '/', $realpath) : $file;
        	} elseif ($GLOBALS['realname'] == 2) {
            	$name = $basename;
        	} else {
            	$name = $file;
        	}
        	$name = htmlspecialchars(str_link($name), ENT_NOQUOTES);
			//
			$pname = '<td><a href="index.php?c=' . $r_file . '/' . $add . '">' . $name . '/</a></td>';
			}
			if($GLOBALS['index']['down']){
				$pdown = '<td> </td>';
			}
			if($GLOBALS['index']['type']){
				$ptype = '<td>' . ($GLOBALS['mode']->is_link($file) ? 'LINK': 'DIR') . '</td>';
			}
			if($GLOBALS['index']['size']){
			//
			if ($GLOBALS['dir_size']) {
  				$isize = $size = size($file, true);
     			$size = format_size($size);
       		} else {
				$isize = $size = $GLOBALS['lng']['unknown'];
			}
            //
			$psize = '<td>' . $size . '</td>';
			}
			if($GLOBALS['index']['change']){
				$pchange = '<td><a href="change.php?' . $r_file . '/">' . $GLOBALS['lng']['ch'] . '</a></td>';
			}
			if($GLOBALS['index']['del']){
				$pdel = '<td><a'.($GLOBALS['del_notify'] ? ' onclick="return confirm(\''.$GLOBALS['lng']['del_notify'].'\')"' : '').' href="change.php?go=del&amp;c=' . $r_file . '/">' . $GLOBALS['lng']['dl'] . '</a></td>';
			}
			if($GLOBALS['index']['chmod']){
				//
				$chmod = look_chmod($file);
				//
				$pchmod = '<td><a href="change.php?go=chmod&amp;c=' . $r_file . '">' . $chmod . '</a></td>';
			}
			if($GLOBALS['index']['date']){
				//
				$time = $GLOBALS['mode']->filemtime($file);
				//
				$pdate = '<td>' . strftime($GLOBALS['date_format'], $time) . '</td>';
			}


		$page1[$key . '_'][$i] = '<td><input name="check[]" type="checkbox" value="' . $r_file . '"/></td>' . $pname . $pdown . $ptype . $psize . $pchange . $pdel . $pchmod . $pdate;
		}
		else{
			if($GLOBALS['index']['name']){
			//
			if ($GLOBALS['realname'] == 1) {
        		$realpath = realpath($file);
        		$name = $realpath ? str_replace('\\', '/', $realpath) : $file;
        	} elseif ($GLOBALS['realname'] == 2) {
            	$name = $basename;
        	} else {
            	$name = $file;
        	}
        	$name = htmlspecialchars(str_link($name), ENT_NOQUOTES);
			//
			if($archive){
				$pname = '<td><a href="index.php?' . $r_file . '">' . $name . '</a><br/><a class="submit" href="change.php?go=1&amp;c=' . $r_file . '&amp;mega_full_extract=1">' . $GLOBALS['lng']['extract_archive'] . '</a></td>';
			}
			else{
				if($type == 'SQL'){
					$pname = '<td><a href="edit.php?' . $r_file . '"' . $target . '>' . $name . '</a><br/><a class="submit" href="change.php?go=tables&amp;c=' . $r_file . '">' . $GLOBALS['lng']['tables'] . '</a><br/><a class="submit" href="change.php?go=installer&amp;c=' . $r_file . '">' . $GLOBALS['lng']['create_sql_installer'] . '</a></td>';
				}
				else{
					$pname = '<td><a href="edit.php?' . $r_file . '"' . $target . '>' . $name . '</a></td>';
				}
			}
		}
		if($GLOBALS['index']['down']){
			$pdown = '<td><a href="change.php?get=' . $r_file . '">' . $GLOBALS['lng']['get'] . '</a></td>';
		}
		if($GLOBALS['index']['type']){
			$ptype = '<td>' . $type . '</td>';
		}
		if($GLOBALS['index']['size']){
			//
			$isize = $size = size($file);
			$size = format_size($size);
			//
			$psize = '<td>' . $size . '</td>';
		}
		if($GLOBALS['index']['change']){
			$pchange = '<td><a href="change.php?' . $r_file . '">' . $GLOBALS['lng']['ch'] . '</a></td>';
		}
		if($GLOBALS['index']['del']){
			$pdel = '<td><a'.($GLOBALS['del_notify'] ? ' onclick="return confirm(\''.$GLOBALS['lng']['del_notify'].'\')"' : '').' href="change.php?go=del&amp;c=' . $r_file . '">' . $GLOBALS['lng']['dl'] . '</a></td>';
		}
		if($GLOBALS['index']['chmod']){
			//
			$chmod = look_chmod($file);
			//
			$pchmod = '<td><a href="change.php?go=chmod&amp;c=' . $r_file . '">' . $chmod . '</a></td>';
		}
		if($GLOBALS['index']['date']){
			//
			$time = $GLOBALS['mode']->filemtime($file);
			//
			$pdate = '<td>' . strftime($GLOBALS['date_format'], $time) . '</td>';
		}


			$page2[$key . '_'][$i] = '<td><input name="check[]" type="checkbox" value="' . $r_file . '"/></td>' . $pname . $pdown . $ptype . $psize . $pchange . $pdel . $pchmod . $pdate;
		}

    }


	$p = array_merge($page1, $page2);

	$a = array_keys($page1);
	$b = array_keys($page2);

	natcasesort($a);
	natcasesort($b);
	if ($down) {
		$a = array_reverse($a, false);
		$b = array_reverse($b, false);
    }

	foreach(array_merge($a, $b) as $var){
		foreach($p[$var] as $f){
			$page[] = $f;
		}
	}

    unset($p, $page1, $page2, $a, $b);


    $all = ceil(sizeof($page) / $GLOBALS['limit']);
    $pg = isset($_GET['pg']) ? intval($_GET['pg']) : 1;
    if ($pg < 1) {
        $pg = 1;
    }
    $page = array_slice($page, ($pg * $GLOBALS['limit']) - $GLOBALS['limit'], $GLOBALS['limit']);


    if ($page) {
        $i = 1;
        $line = false;
        
        if($GLOBALS['index']['n']){
        	foreach ($page as $var) {
  	    		$line = !$line;
    			if($line){
    				echo '<tr class="border">' . $var . '<td>' . ($i++) . '</td></tr>';
   				}
   				else{
   					echo '<tr class="border2">' . $var . '<td>' . ($i++) . '</td></tr>';
				}
        	}
        }
        else{
        	foreach ($page as $var) {
  	    		$line = !$line;
    			if($line){
    				echo '<tr class="border">' . $var . '</tr>';
   				}
   				else{
   					echo '<tr class="border2">' . $var . '</tr>';
				}
        	}
       	}
    } else {
        echo '<tr class="border"><th colspan="' . (array_sum($GLOBALS['index']) + 1) . '">' . $GLOBALS['lng']['dir_empty'] . '</th></tr>';
    }

    echo go($pg, $all, '&amp;c=' . $current . $out . $add);
}


function copy_d($dest = '', $source = '', $to = '')
{
	$ex = explode('/', $source);
	$tmp1 = $tmp2 = '';

	foreach(explode('/', $to) as $var){
		$ch = each($ex);
		$tmp1 .= $var . '/';
		$tmp2 .= $ch[1] . '/';

		if(!$GLOBALS['mode']->is_dir($tmp1)){
			$GLOBALS['mode']->mkdir($tmp1, look_chmod($tmp2));
		}
	}
}


function copy_files($d = '', $dest = '', $static = '')
{

    foreach($GLOBALS['mode']->iterator($d) as $file) {
        if ($file == $static) {
            continue;
        }
        if ($d == $dest) {
            break;
        }

        $ch = look_chmod($d . '/' . $file);

        if ($GLOBALS['mode']->is_dir($d . '/' . $file)) {

            $GLOBALS['mode']->mkdir($dest . '/' . $file, $ch);
            $GLOBALS['mode']->chmod($dest, $ch);
            copy_files($d . '/' . $file, $dest . '/' . $file, $static);
        } else {
            $GLOBALS['mode']->copy($d . '/' . $file, $dest . '/' . $file, $ch);
        }
    }

    return report(str_replace('%dir%', htmlspecialchars($dest, ENT_NOQUOTES), $GLOBALS['lng']['copy_files_true']), false);
}


function move_files($d = '', $dest = '', $static = '')
{

    foreach($GLOBALS['mode']->iterator($d) as $file) {
        if ($file == $static) {
            continue;
        }
        if ($d == $dest) {
            break;
        }

        $ch = look_chmod($d . '/' . $file);

        if ($GLOBALS['mode']->is_dir($d . '/' . $file)) {

            $GLOBALS['mode']->mkdir($dest . '/' . $file, $ch);
            $GLOBALS['mode']->chmod($dest . '/' . $file, $ch);
            move_files($d . '/' . $file, $dest . '/' . $file, $static);
            $GLOBALS['mode']->rmdir($d . '/' . $file);
        } else {
            if ($GLOBALS['mode']->copy($d . '/' . $file, $dest . '/' . $file, $ch)) {
                $GLOBALS['mode']->unlink($d . '/' . $file);
            }
        }
    }

    $GLOBALS['mode']->rmdir($d);

    return report(str_replace('%dir%', htmlspecialchars($dest, ENT_NOQUOTES), $GLOBALS['lng']['move_files_true']), false);
}


function copy_file($source = '', $dest = '', $chmod = '' /* 0644 */)
{
	if($source == $dest){
		if($chmod){
			rechmod($dest, $chmod);
		}
		return report(htmlspecialchars(str_replace('%file%', $source, $GLOBALS['lng']['move_file_true'])), false);
	}

	$d = dirname($dest);
    copy_d($d, dirname($source), $d);

    if ($GLOBALS['mode']->copy($source, $dest)) {
        if (!$chmod) {
        	$chmod = look_chmod($source);
       	}
			rechmod($dest, $chmod);

        return report(htmlspecialchars(str_replace('%file%', $source, $GLOBALS['lng']['copy_file_true'])), false);
    } else {
    	$error = error();
        return report(htmlspecialchars(str_replace('%file%', $source, $GLOBALS['lng']['copy_file_false'])) . '<br/>' . $error, true);
    }
}


function move_file($source = '', $dest = '', $chmod = '' /* 0644 */)
{
	if($source == $dest){
		if($chmod){
			rechmod($dest, $chmod);
		}
		return report(htmlspecialchars(str_replace('%file%', $source, $GLOBALS['lng']['move_file_true'])), false);
	}

	$d = dirname($dest);
    copy_d($d, dirname($source), $d);

    if ($GLOBALS['mode']->copy($source, $dest)) {
        if (!$chmod) {
			$chmod = look_chmod($source);
        }

        rechmod($dest, $chmod);
        del_file($source);

        return report(htmlspecialchars(str_replace('%file%', $source, $GLOBALS['lng']['move_file_true'])), false);
    } else {
    	$error = error();
        return report(htmlspecialchars(str_replace('%file%', $source, $GLOBALS['lng']['move_file_false'])) . '<br/>' . $error, true);
    }
}


function del_file($f = '')
{
    //$f = rawurldecode($f);

    if ($GLOBALS['mode']->unlink($f)) {
        return report($GLOBALS['lng']['del_file_true'] . ' (' . htmlspecialchars($f, ENT_NOQUOTES) . ')', false);
    } else {
    	$error = error();
        return report($GLOBALS['lng']['del_file_false'] . ' (' . htmlspecialchars($f, ENT_NOQUOTES) . ')<br/>' . $error, true);
    }
}


function del_dir($d = '')
{
    $err = '';
    //$d = rawurldecode($d);

    $GLOBALS['mode']->chmod($d, '0777');

    foreach($GLOBALS['mode']->iterator($d) as $f) {

		$realpath = realpath($d . '/' . $f);
		$f = $realpath ? str_replace('\\', '/', $realpath) : str_replace('//', '/', $d . '/' . $f);
        $GLOBALS['mode']->chmod($f, '0777');
        if ($GLOBALS['mode']->is_file($f) || $GLOBALS['mode']->is_link($f)) {
            if (!$GLOBALS['mode']->unlink($f)) {
                $err .= $f . '<br/>';
            }
        } elseif ($GLOBALS['mode']->is_dir($f)) {
            //$GLOBALS['mode']->rmdir($f);
            del_dir($f . '/');
        }
    }

    if (!$GLOBALS['mode']->rmdir($d)) {
        $err .= error() . '<br/>';
    }
    if ($err) {
        return report($GLOBALS['lng']['del_dir_false'] . '<br/>' . $err, true);
    }
    return report($GLOBALS['lng']['del_dir_true'] . ' (' . htmlspecialchars($d, ENT_NOQUOTES) . ')', false);
}


function size($source = '', $is_dir = false)
{
    if ($is_dir) {
	$ds = array($source);
    $sz = 0;
    do {
        $d = array_shift($ds);

        foreach($GLOBALS['mode']->iterator($d) as $file) {
                if ($GLOBALS['mode']->is_dir($d . '/' . $file)) {
                    $ds[] = $d . '/' . $file;
                }
                else{
                	$sz += $GLOBALS['mode']->filesize($d . '/' . $file);
            	}
        }
    } while (sizeof($ds) > 0);

    return $sz;
    }
	else {
        return $GLOBALS['mode']->filesize($source);
    }
}


function format_size($size = '', $int = 2){
	if ($size < 1024) {
        return $size . ' Byte';
    } else if ($size < 1048576) {
        return round($size / 1024, $int) . ' Kb';
    }
    else if ($size < 1073741824) {
    	return round($size / 1024 / 1024, $int) . ' Mb';
   	}
   	else {
		return round($size / 1024 / 1024 / 1024, $int) . ' Gb';
	}
}


function look_chmod($file = '')
{
    return substr(sprintf('%o', $GLOBALS['mode']->fileperms($file)), -4);
}


function create_file($file = '', $text = '', $chmod = '0644')
{
	create_dir(dirname($file));

    if ($GLOBALS['mode']->file_put_contents($file, $text)) {
        return report($GLOBALS['lng']['fputs_file_true'], false) . rechmod($file, $chmod);
    }
    else{
   		$error = error();
		return report($GLOBALS['lng']['fputs_file_false'] . '<br/>' . $error, true);
   	}
}

function rechmod($current = '', $chmod = '0755')
{
    //$current = rawurldecode($current);

    settype($chmod, 'string');
    $strlen = strlen($chmod);

	if(!ctype_digit($chmod) || ($strlen != 3 && $strlen != 4)){
		return report($GLOBALS['lng']['chmod_mode_false'], true);
	}

    if ($strlen == 3) {
        $chmod = '0' . $chmod;
    }

    if ($GLOBALS['mode']->chmod($current, $chmod)) {
        return report($GLOBALS['lng']['chmod_true'] . ' (' . htmlspecialchars($current, ENT_NOQUOTES) . ' : ' . $chmod . ')', false);
    } else {
    	$error = error();
        return report($GLOBALS['lng']['chmod_false'] . ' (' . htmlspecialchars($current, ENT_NOQUOTES) . ')<br/>' . $error, true);
    }
}


function create_dir($dir = '', $chmod = '0755')
{
	$tmp = $tmp2 = $err = '';
	$i = 0;
	$g = explode(DIRECTORY_SEPARATOR, getcwd());

	foreach(explode('/', $dir) as $d){
		$tmp .= $d . '/';
		$tmp2 .= $g[$i] . '/';

		if($tmp == $tmp2 || $GLOBALS['mode']->is_dir($tmp)){
			$i++;
			continue;
		}
		if(!$GLOBALS['mode']->mkdir($tmp, $chmod)){
			$err .= error() . ' ('.htmlspecialchars($tmp, ENT_NOQUOTES).')<br/>';
		}
		$i++;
	}

    if ($err) {
    	return report($GLOBALS['lng']['create_dir_false'] . '<br/>' . $err, true);
    } else {
    	return report($GLOBALS['lng']['create_dir_true'], false);
    }
}


function frename($current = '', $name = '', $chmod = '' /* 0644 */, $del = '', $to = '')
{
	// $current = rawurldecode($current);

    if ($GLOBALS['mode']->is_dir($current)) {
        copy_d($name, $current, $to);

        if ($del) {
            return move_files($current, $name, static_name($current, $name));
        } else {
            return copy_files($current, $name, static_name($current, $name));
        }
    } else {
        if ($del) {
            return move_file($current, $name, $chmod);
        } else {
            return copy_file($current, $name, $chmod);
        }
    }
}


function syntax($source = '', $charset = array())
{
    if (!$GLOBALS['mode']->is_file($source)) {
        return report($GLOBALS['lng']['not_found'], true);
    }

    exec(escapeshellcmd($GLOBALS['php']) . ' -c -f -l "' . escapeshellarg($source) . '"', $rt, $v);
	$error = error();

    if (!sizeof($rt)) {
        return report($GLOBALS['lng']['syntax_not_check'] . '<br/>' . $error, true);
    }

    if (($v == 255) || (sizeof($rt) > 2)) {
        $st = trim(strip_tags($rt[1]));
        if ($st != null) {
            $erl = preg_replace('/.*\s(\d*)$/', '$1', $st, 1);
            $pg = $st;
        } else {
            $pg = $GLOBALS['lng']['syntax_unknown'] . '<br/>';
        }
    } elseif (($v == 0) || (sizeof($rt) > 0)) {
        $pg = $GLOBALS['lng']['syntax_true'] . '<br/>';
    }

    $fl = trim($GLOBALS['mode']->file_get_contents($source));
    if ($charset[0]) {
        $fl = iconv($charset[0], $charset[1], $fl);
    }

    if (substr_count($fl, "\r") > 2) {
        $arr = explode("\r", xhtml_highlight(str_replace("\n", '', $fl)));
    } else {
        $arr = explode('<br />', xhtml_highlight($fl));
    }

    for ($i = 0, $end = sizeof($arr); $i < $end; ++$i) {
        if ($i == ($erl - 1)) {
            $page .= '<span class="fail_code">&#160;' . ($i + 1) . '&#160;</span> ' . $arr[$i] .
                '<br/>';
        } else {
            $page .= '<span class="true_code">' . ($i + 1) . '</span> ' . $arr[$i] . '<br/>';
        }
    }

    return report($pg, false).'<div class="code">' . $page . '</div>';
}


function syntax2($current = '', $charset = array())
{
	if(!$charset[0]){
		$charset[0] = 'UTF-8';
	}
    $fp = fsockopen('wapinet.ru', 80, $er1, $er2, 10);
    if (!$fp) {
    	$error = error();
        return report($GLOBALS['lng']['syntax_not_check'] . '<br/>' . $error, true);
    }

    $f = rawurlencode(trim($GLOBALS['mode']->file_get_contents($current)));

    fputs($fp, 'POST /syntax2/index.php HTTP/1.0' . "\r\n" .
        'Content-type: application/x-www-form-urlencoded; charset=' . $charset[0] . "\r\n" .
        'Content-length: ' . (iconv_strlen($f) + 2) . "\r\n" .
		'Host: wapinet.ru' . "\r\n" .
        'Connection: close' . "\r\n" .
		'User-Agent: GManager ' . $GLOBALS['version'] . "\r\n\r\n" .
        'f=' . $f . "\r\n\r\n");

    while ($r != "\r\n") {
        $r = fgets($fp);
    }
    $r = '';

    while (!feof($fp)) {
        $r .= fread($fp, 1024);
    }
    fclose($fp);
    return trim($r);
}


function zip_syntax($current = '', $f = '', $charset = array(), $syntax = '')
{
    $content = edit_zip_file($current, $f);

    $tmp = dirname(__FILE__).'/data/GmanagerSyntax'.time().'.tmp';
    $fp = fopen($tmp, 'w');

    if(!$fp){
    	$error = error();
    	return report($GLOBALS['lng']['syntax_not_check'] . '<br/>' . $error, true);
   	}

    fputs($fp, $content['text']);
    fclose($fp);

    if (!$syntax) {
        $pg = syntax($tmp, $charset);
    } else {
        $pg = syntax2($tmp, $charset);
    }
    unlink($tmp);

    return $pg;
}


function validator($current = '', $charset = array())
{
	if(!extension_loaded('xml')){
		return report($GLOBALS['lng']['disable_function'] . ' (xml)', true);
	}

    $fl = $GLOBALS['mode']->file_get_contents($current);
    if ($charset[0]) {
        $fl = iconv($charset[0], $charset[1], $fl);
    } 

    $xml_parser = xml_parser_create();
    if (!xml_parse($xml_parser, $fl)) {
        $err = xml_error_string(xml_get_error_code($xml_parser));
        $line = xml_get_current_line_number($xml_parser);
        $column = xml_get_current_column_number($xml_parser);
        xml_parser_free($xml_parser);
        return report('Error [Line ' . $line . ', Column ' . $column . ']: ' . $err, true) . code($fl, $line);
    } else {
        xml_parser_free($xml_parser);
        return report($GLOBALS['lng']['validator_true'], false) . code($fl, 0);
    }
}


function xhtml_highlight($fl = '')
{
    return preg_replace('#color="(.*?)"#', 'style="color: $1"', str_replace(array('<font ', '</font>'), array('<span ', '</span>'), highlight_string($fl, true)));
}


function url_highlight($fl = '')
{
    return '<code>' . nl2br(preg_replace("~(&quot;|&#039;)[^<>]*(&quot;|&#039;)~iU",
        '<span style="color:#DD0000">$0</span>', preg_replace("~&lt;!--.*--&gt;~iU",
        '<span style="color:#FF8000">$0</span>', preg_replace("~(&lt;[^\s!]*\s)([^<>]*)([/?]?&gt;)~iU",
        '$1<span style="color:#007700">$2</span>$3', preg_replace("~&lt;[^<>]*&gt;~iU",
        '<span style="color:#0000BB">$0</span>', htmlspecialchars($fl, ENT_QUOTES)))))) .
        '</code>';
}


function code($fl = '', $line = 0)
{
	$page = '';

    if (substr_count($fl, "\r") > 2) {
        $arr = explode("\r", url_highlight(str_replace("\n", '', $fl)));
    } else {
        $arr = explode("\n", url_highlight($fl));
    }

    for ($i = 0, $end = sizeof($arr); $i < $end; ++$i) {
        if ($i == ($line - 1)) {
            $page .= '<span class="fail_code">&#160;' . ($i + 1) . '&#160;</span> ' . $arr[$i];
        } else {
            $page .= '<span class="true_code">' . ($i + 1) . '</span> ' . $arr[$i];
        }
    }
    return '<div class="code">' . $page . '</div>';
}


function list_zip_archive($current = '', $down = '')
{
    require_once $GLOBALS['pclzip'];
    $r_current = str_replace('%2F', '/', rawurlencode($current));

    $zip = new PclZip($GLOBALS['class'] == 'ftp' ? ftp_archive_start($current) : $current);

    if (!$list = $zip->listContent()) {
    	if($GLOBALS['class'] == 'ftp'){
			ftp_archive_end('');
		}
        return '<tr class="border"><td colspan="' . (array_sum($GLOBALS['index']) + 1) . '">' . report($GLOBALS['lng']['archive_error'] . '<br/>' . $zip->errorInfo(true), true) . '</td></tr>';
    } else {
		$link = '';

		if($down){
			$list = array_reverse($list);
		}

        for ($i = 0, $s = sizeof($list); $i < $s; ++$i) {

        	$r_name = str_replace('%2F', '/', rawurlencode($list[$i]['filename']));

            if ($list[$i]['folder']) {
                $type = 'DIR';
                $name = htmlspecialchars($list[$i]['filename'], ENT_NOQUOTES);
                $size = '';
            } else {
                $type = htmlspecialchars(get_type($list[$i]['filename']), ENT_NOQUOTES);
                $name = '<a href="?c=' . $r_current . '&amp;f=' . $r_name . '">' . htmlspecialchars(str_link($list[$i]['filename']), ENT_NOQUOTES) . '</a>';
                $size = format_size($list[$i]['size']);
            }

            $link .= '<tr class="border"><td><input name="check[]" type="checkbox" value="' . $r_name . '"/></td>';
            if($GLOBALS['index']['name']){
				$link .= '<td>' . $name . '</td>';
			}
			if($GLOBALS['index']['down']){
				$link .= '<td><a href="change.php?get=' . $r_current . '&amp;f=' . $r_name . '">' . $GLOBALS['lng']['get'] . '</a></td>';
			}
			if($GLOBALS['index']['type']){
				$link .= '<td>' . $type . '</td>';
			}
			if($GLOBALS['index']['size']){
				$link .= '<td>' . $size . '</td>';
			}
			if($GLOBALS['index']['change']){
				$link .= '<td> </td>';
			}
			if($GLOBALS['index']['del']){
				$link .= '<td><a'.($GLOBALS['del_notify'] ? ' onclick="return confirm(\''.$GLOBALS['lng']['del_notify'].'\')"' : '').' href="change.php?go=del_zip_archive&amp;c=' . $r_current . '&amp;f=' . $r_name . '">' . $GLOBALS['lng']['dl'] . '</a></td>';
			}
			if($GLOBALS['index']['chmod']){
				$link .= '<td> </td>';
			}
			if($GLOBALS['index']['date']){
				$link .= '<td>' . strftime($GLOBALS['date_format'], $list[$i]['mtime']) . '</td>';
			}
			if($GLOBALS['index']['n']){
				$link .= '<td>' . ($i + 1) . '</td>';
			}

			$link .= '</tr>';
        }

		if($GLOBALS['class'] == 'ftp'){
			ftp_archive_end();
		}

		$prop = $zip->properties();
		if($prop['comment']){
			if(iconv('UTF-8', 'UTF-8', $prop['comment']) != $prop['comment']){
				$prop['comment'] = iconv('Windows-1251', 'UTF-8', $prop['comment']);
			}
			$link .= '<tr class="border"><td>' . $GLOBALS['lng']['comment_archive'] . '</td><td colspan="' . (array_sum($GLOBALS['index']) + 1) . '"><pre>' . htmlspecialchars($prop['comment'], ENT_NOQUOTES) . '</pre></td></tr>';
		}

        return $link;
    }
}


function list_tar_archive($current = '', $down = '')
{
    require_once $GLOBALS['tar'];

    $tar = new Archive_Tar($GLOBALS['class'] == 'ftp' ? ftp_archive_start($current) : $current);

    if (!$list = $tar->listContent()) {
    	if($GLOBALS['class'] == 'ftp'){
			ftp_archive_end('');
		}
        return '<tr class="border"><td colspan="' . (array_sum($GLOBALS['index']) + 1) . '">' . report($GLOBALS['lng']['archive_error'], true) . '</td></tr>';
    } else {
		$r_current = str_replace('%2F', '/', rawurlencode($current));
		$link = '';

		if($down){
			$list = array_reverse($list);
		}

        for ($i = 0, $s = sizeof($list); $i < $s; ++$i) {
        	$r_name = rawurlencode($list[$i]['filename']);

            if ($list[$i]['typeflag']) {
                $type = 'DIR';
                $name = htmlspecialchars($list[$i]['filename'], ENT_NOQUOTES);
                $size = ' ';
            } else {
                $type = htmlspecialchars(get_type($list[$i]['filename']), ENT_NOQUOTES);
                $name = '<a href="?c=' . $r_current . '&amp;f=' . $r_name . '">' . htmlspecialchars(str_link($list[$i]['filename']), ENT_NOQUOTES) . '</a>';
                $size = format_size($list[$i]['size']);
            }
            $link .= '<tr class="border"><td><input name="check[]" type="checkbox" value="' . $r_name . '"/></td>';
			if($GLOBALS['index']['name']){
				$link .= '<td>' . $name . '</td>';
			}
			if($GLOBALS['index']['down']){
				$link .= '<td><a href="change.php?get=' . $r_current . '&amp;f=' . $r_name . '">' . $GLOBALS['lng']['get'] . '</a></td>';
			}
			if($GLOBALS['index']['type']){
				$link .= '<td>' . $type . '</td>';
			}
			if($GLOBALS['index']['size']){
				$link .= '<td>' . $size . '</td>';
			}
			if($GLOBALS['index']['change']){
				$link .= '<td> </td>';
			}
			if($GLOBALS['index']['del']){
				$link .= '<td><a'.($GLOBALS['del_notify'] ? ' onclick="return confirm(\''.$GLOBALS['lng']['del_notify'].'\')"' : '').' href="change.php?go=del_tar_archive&amp;c=' . $r_current . '&amp;f=' . $r_name . '">' . $GLOBALS['lng']['dl'] . '</a></td>';
			}
			if($GLOBALS['index']['chmod']){
				$link .= '<td> </td>';
			}
			if($GLOBALS['index']['date']){
				$link .= '<td>' . strftime($GLOBALS['date_format'], $list[$i]['mtime']) . '</td>';
			}
			if($GLOBALS['index']['n']){
				$link .= '<td>' . ($i + 1) . '</td>';
			}

			$link .= '</tr>';
        }

		if($GLOBALS['class'] == 'ftp'){
			ftp_archive_end();
		}

        return $link;
    }
}


function edit_zip_file($current = '', $f = '')
{
    require_once $GLOBALS['pclzip'];

    $zip = new PclZip($GLOBALS['class'] == 'ftp' ? ftp_archive_start($current) : $current);
    $ext = $zip->extract(PCLZIP_OPT_BY_NAME, $f, PCLZIP_OPT_EXTRACT_AS_STRING);

	if($GLOBALS['class'] == 'ftp'){
		ftp_archive_end('');
	}

    if (!$ext) {
        return array('text' => $GLOBALS['lng']['archive_error'], 'size' => 0, 'lines' => 0);
    } else {
        return array('text' => trim($ext[0]['content']), 'size' => format_size($ext[0]['size']), 'lines' => sizeof(explode("\n", $ext[0]['content'])));
    }
}


function edit_zip_file_ok($current = '', $f = '', $text = '')
{
    require_once $GLOBALS['pclzip'];

    define('PCLZIP_TMP_NAME', $f);
    $tmp = dirname(__FILE__).'/data/GmanagerArchivers'.time().'.tmp';

    $fp = fopen($tmp, 'w');
    
    if(!$fp){
    	$error = error();
    	return report($GLOBALS['lng']['fputs_file_false'] . '<br/>' . $error, true);
   	}
    
    fputs($fp, $text);
    fclose($fp);

    $zip = new PclZip($GLOBALS['class'] == 'ftp' ? ftp_archive_start($current) : $current);

    if($zip->delete(PCLZIP_OPT_BY_NAME, $f) == 0){
    	if($GLOBALS['class'] == 'ftp'){
    		ftp_archive_end('');
   		}
    	unlink($tmp);
    	return report($GLOBALS['lng']['fputs_file_false'] . '<br/>' . $zip->errorInfo(true), true);
   	}

    function cb($p_event, &$p_header)
    {
        $p_header['stored_filename'] = PCLZIP_TMP_NAME;
        return 1;
    }

    $fl = $zip->add($tmp, PCLZIP_CB_PRE_ADD, 'cb');

    unlink($tmp);
    if($GLOBALS['class'] == 'ftp'){
		ftp_archive_end($current);
	}

    if ($fl) {
        return report($GLOBALS['lng']['fputs_file_true'], false);
    } else {
        return report($GLOBALS['lng']['fputs_file_false'], true);
    }
}


function look_zip_file($current = '', $f = '')
{
	require_once $GLOBALS['pclzip'];
	$r_current = str_replace('%2F', '/', rawurlencode($current));
	$r_f = str_replace('%2F', '/', rawurlencode($f));

    $zip = new PclZip($GLOBALS['class'] == 'ftp' ? ftp_archive_start($current) : $current);
    $ext = $zip->extract(PCLZIP_OPT_BY_NAME, $f, PCLZIP_OPT_EXTRACT_AS_STRING);

	if($GLOBALS['class'] == 'ftp'){
	    ftp_archive_end('');
	}

    if (!$ext) {
        return report($GLOBALS['lng']['archive_error'], true);
    }
    else if($ext[0]['status'] == 'unsupported_encryption') {
    	return report($GLOBALS['lng']['archive_error_encrypt'], true);
   	}
	else {
        return report($GLOBALS['lng']['archive_size'] . ': ' . format_size($ext[0]['compressed_size']) . '<br/>' . $GLOBALS['lng']['real_size'] . ': ' . format_size($ext[0]['size']) . '<br/>' . $GLOBALS['lng']['archive_date'] . ': ' . strftime($GLOBALS['date_format'], $ext[0]['mtime']) . '<br/>&#187;<a href="edit.php?c=' . $r_current . '&amp;f=' . $r_f . '">' . $GLOBALS['lng']['edit'] . '</a>', false) . archive_fl(trim($ext[0]['content']));
    }
}


function look_tar_file($current = '', $f = '')
{
    require_once $GLOBALS['tar'];

    $tar = new Archive_Tar($GLOBALS['class'] == 'ftp' ? ftp_archive_start($current) : $current);
	$ext = $tar->extractInString($f);


    if (!$ext) {
    	if($GLOBALS['class'] == 'ftp'){
	    	ftp_archive_end('');
		}
        return report($GLOBALS['lng']['archive_error'], true);
    }
	else {
        $list = $tar->listContent();

        if($GLOBALS['class'] == 'ftp'){
	    	ftp_archive_end('');
		}

        for ($i = 0, $s = sizeof($list); $i < $s; ++$i) {
            if ($list[$i]['filename'] != $f) {
                continue;
            }
			else {
                return report($GLOBALS['lng']['real_size'] . ': ' . format_size($list[$i]['size']) . '<br/>' . $GLOBALS['lng']['archive_date'] . ': ' . strftime($GLOBALS['date_format'], $list[$i]['mtime']), false) . archive_fl(trim($ext));
            }
        }
    }
}


function extract_zip_archive($current = '', $name = '', $chmod = array())
{
    require_once $GLOBALS['pclzip'];

   	if($GLOBALS['class'] == 'ftp'){
   		$name = ($name[0] == '/' ? $name : dirname($current . '/') . '/' . $name);
    	$ftp_current = dirname(__FILE__).'/data/GmanagerFtpZip'.time().'.tmp';
    	$ftp_name = dirname(__FILE__).'/data/GmanagerZipFtp'.time().'/';
    	mkdir($ftp_name, 0777);
    	file_put_contents($ftp_current, $GLOBALS['mode']->file_get_contents($current));
   	}


    define('CHMODF', $chmod[0]); // CHMOD to files
    define('CHMODD', $chmod[1]); // CHMOD to folders

	function callback_post_extract($p_event, &$p_header){
		if($GLOBALS['mode']->is_dir($p_header['filename'])){
			rechmod($p_header['filename'], CHMODD);
		}
		else{
			rechmod($p_header['filename'], CHMODF);
		}
		return 1;
	}


	$zip = new PclZip($GLOBALS['class'] == 'ftp' ? $ftp_current : $current);
	if(!$zip->extract(PCLZIP_OPT_PATH, $GLOBALS['class'] == 'ftp' ? $ftp_name : $name, PCLZIP_CB_POST_EXTRACT, 'callback_post_extract')){
		if($GLOBALS['class'] == 'ftp'){
			unlink($ftp_current);
			rmdir($ftp_name);
		}
		return report($GLOBALS['lng']['extract_false'] . '<br/>' . $zip->errorInfo(true), true);
	}

	if($GLOBALS['class'] == 'ftp'){
		create_dir($name, CHMODD);
		ftp_move_files($ftp_name, $name, CHMODF, CHMODD);
    	unlink($ftp_current);
   	}

    if ($GLOBALS['mode']->is_dir($name) || $GLOBALS['class'] == 'ftp') {
        if ($chmod) {
            rechmod($name, $chmod[1]);
        }
        return report($GLOBALS['lng']['extract_true'], false);
    } else {
        return report($GLOBALS['lng']['extract_false'], true);
    }
}


function extract_tar_archive($current = '', $name = '', $chmod = array())
{
    require_once $GLOBALS['tar'];

    if($GLOBALS['class'] == 'ftp'){
    	$name = ($name[0] == '/' ? $name : dirname($current . '/') . '/' . $name);
    	$ftp_current = dirname(__FILE__).'/data/GmanagerFtpTar'.time().'.tmp';
    	$ftp_name = dirname(__FILE__).'/data/GmanagerZipTar'.time().'/';
    	mkdir($ftp_name, 0777);
    	file_put_contents($ftp_current, $GLOBALS['mode']->file_get_contents($current));
   	}

    $tar = new Archive_Tar($GLOBALS['class'] == 'ftp' ? $ftp_current : $current);

    if(!$tar->extract($GLOBALS['class'] == 'ftp' ? $ftp_name : $name)){
    	if($GLOBALS['class'] == 'ftp'){
			unlink($ftp_current);
			rmdir($ftp_name);
		}
    	return report($GLOBALS['lng']['extract_false'], true);
   	}

    foreach($tar->listContent() as $var){
    	if($GLOBALS['mode']->is_dir($name.'/'.$var['filename'])){
    		rechmod($name.'/'.$var['filename'], $chmod[1]);
   		}
   		else{
   			rechmod($name.'/'.$var['filename'], $chmod[0]);
		}
   	}

   	if($GLOBALS['class'] == 'ftp'){
		create_dir($name, $chmod[1]);
		ftp_move_files($ftp_name, $name, $chmod[0], $chmod[1]);
    	unlink($ftp_current);
   	}

    if ($GLOBALS['mode']->is_dir($name) || $GLOBALS['class'] == 'ftp') {
            rechmod($name, $chmod[1]);
        return report($GLOBALS['lng']['extract_true'], false);
    } else {
        return report($GLOBALS['lng']['extract_false'], true);
    }
}


function extract_zip_file($current = '', $name = '', $chmod = '0755', $ext = '')
{
    require_once $GLOBALS['pclzip'];

   	if($GLOBALS['class'] == 'ftp'){
   		$name = ($name[0] == '/' ? $name : dirname($current . '/') . '/' . $name);
    	$ftp_current = dirname(__FILE__).'/data/GmanagerFtpZipArchive'.time().'.tmp';
    	$ftp_name = dirname(__FILE__).'/data/GmanagerFtpZipFile'.time().'.tmp';
    	file_put_contents($ftp_current, $GLOBALS['mode']->file_get_contents($current));
   	}

    $zip = new PclZip($GLOBALS['class'] == 'ftp' ? $ftp_current : $current);
    if(!$zip->extract(PCLZIP_OPT_PATH, $GLOBALS['class'] == 'ftp' ? $ftp_name : $name, PCLZIP_OPT_BY_NAME, $ext)){
    	if($GLOBALS['class'] == 'ftp'){
    		unlink($ftp_current);
   		}
    	return report($GLOBALS['lng']['extract_file_false'] . '<br/>' . $zip->errorInfo(true), true);
   	}

    if($GLOBALS['class'] == 'ftp'){
    	create_dir($name);
    	ftp_move_files($ftp_name, $name);
    	unlink($ftp_current);
   	}

    if ($GLOBALS['mode']->is_dir($name) || $GLOBALS['class'] == 'ftp') {
        if ($chmod) {
            rechmod($name, $chmod);
        }
        return report($GLOBALS['lng']['extract_file_true'], false);
    } else {
        return report($GLOBALS['lng']['extract_file_false'], true);
    }
}


function extract_tar_file($current = '', $name = '', $chmod = '0755', $ext = '')
{
    require_once $GLOBALS['tar'];

	if($GLOBALS['class'] == 'ftp'){
   		$name = ($name[0] == '/' ? $name : dirname($current . '/') . '/' . $name);
    	$ftp_current = dirname(__FILE__).'/data/GmanagerFtpTarArchive'.time().'.tmp';
    	$ftp_name = dirname(__FILE__).'/data/GmanagerFtpTarFile'.time().'.tmp';
    	file_put_contents($ftp_current, $GLOBALS['mode']->file_get_contents($current));
   	}

    $tar = new Archive_Tar($GLOBALS['class'] == 'ftp' ? $ftp_current : $current);
    
    if(!$tar->extractList($ext, $GLOBALS['class'] == 'ftp' ? $ftp_name : $name)){
    	if($GLOBALS['class'] == 'ftp'){
    		unlink($ftp_current);
   		}
    	return report($GLOBALS['lng']['extract_file_false'], true);
   	}

	if($GLOBALS['class'] == 'ftp'){
    	create_dir($name);
    	ftp_move_files($ftp_name, $name);
    	unlink($ftp_current);
   	}

    if ($GLOBALS['mode']->is_dir($name) || $GLOBALS['class'] == 'ftp') {
        if ($chmod) {
            rechmod($name, $chmod);
        }
        return report($GLOBALS['lng']['extract_file_true'], false);
    } else {
        return report($GLOBALS['lng']['extract_file_false'], true);
    }
}


function del_zip_archive($current = '', $f = '')
{
    require_once $GLOBALS['pclzip'];

    $zip = new PclZip($GLOBALS['class'] == 'ftp' ? ftp_archive_start($current) : $current);
    $list = $zip->delete(PCLZIP_OPT_BY_NAME, $f);

    if($GLOBALS['class'] == 'ftp'){
    	ftp_archive_end($current);
   	}

    if ($list != 0) {
    	return report($GLOBALS['lng']['del_file_true'], false);
    } else {
        return report($GLOBALS['lng']['del_file_false'] . '<br/>' . $zip->errorInfo(true), true);
    }
}


function del_tar_archive($current = '', $f = '')
{
    require_once $GLOBALS['tar'];

    $tar = new Archive_Tar($GLOBALS['class'] == 'ftp' ? ftp_archive_start($current) : $current);

    $list = $tar->listContent();

    $new_tar = $new_tar_string = array();

    for ($i = 0, $s = sizeof($list); $i < $s; ++$i) {
        if ($list[$i]['filename'] == $f) {
            continue;
        } else {
            $new_tar_string[] = $tar->extractInString($list[$i]['filename']);
            $new_tar[] = $list[$i]['filename'];
        }
    }

	$GLOBALS['mode']->file_put_contents($current, '');

    for ($i = 0, $s = sizeof($new_tar); $i < $s; ++$i) {
    	$l = strrev($new_tar[$i]);
        if ($l[0] == '/') {
        	$l[0] = '';
            $tar->addModify('.', strrev($l));
        } else {
            $tar->addString($new_tar[$i], $new_tar_string[$i]);
        }
    }

	unset($new_tar_string, $new_tar);
	$list = $tar->listContent();

	if($GLOBALS['class'] == 'ftp'){
    	ftp_archive_end($current);
   	}

    if (in_array($f, $list)) {
        return report($GLOBALS['lng']['del_file_false'], true);
    } else {
        return report($GLOBALS['lng']['del_file_true'], false);
    }
}


function add_archive($c = '')
{
$current = dirname($c) . '/';
$r_current = str_replace('%2F', '/', rawurlencode($current));

echo '<form action="change.php?c=' . $r_current . '&amp;go=1" method="post">
<div class="telo">
<table>
<tr>
<th>' . $GLOBALS['lng']['ch_index'] . '</th>
' . ($GLOBALS['index']['name'] ? '<th>' . $GLOBALS['lng']['name'] . '</th>' : '') . '
' . ($GLOBALS['index']['type'] ? '<th>' . $GLOBALS['lng']['type'] . '</th>' : '') . '
' . ($GLOBALS['index']['size'] ? '<th>' . $GLOBALS['lng']['size'] . '</th>' : '') . '
' . ($GLOBALS['index']['change'] ? '<th>' . $GLOBALS['lng']['change'] . '</th>' : '') . '
' . ($GLOBALS['index']['del'] ? '<th>' . $GLOBALS['lng']['del'] . '</th>' : '') . '
' . ($GLOBALS['index']['chmod'] ? '<th>' . $GLOBALS['lng']['chmod'] . '</th>' : '') . '
' . ($GLOBALS['index']['date'] ? '<th>' . $GLOBALS['lng']['date'] . '</th>' : '') . '
' . ($GLOBALS['index']['n'] ? '<th>' . $GLOBALS['lng']['n'] . '</th>' : '') . '
</tr>';

echo look($current);

echo '</table>
<div class="ch">
<input type="submit" name="add_archive" value="' . $GLOBALS['lng']['add_archive'] . '"/>
</div>
</div>
</form>
<div class="rb">' . $GLOBALS['lng']['create'] . '
<a href="change.php?go=create_file&amp;c=' . $r_current . '">' . $GLOBALS['lng']['file'] . '</a> / <a href="change.php?go=create_dir&amp;c=' . $r_current . '">' . $GLOBALS['lng']['dir'] . '</a><br/>
</div>
<div class="rb">
<a href="change.php?go=upload&amp;c=' . $r_current . '">' . $GLOBALS['lng']['upload'] . '</a><br/>
</div>
<div class="rb">
<a href="change.php?go=mod&amp;c=' . $r_current . '">' . $GLOBALS['lng']['mod'] . '</a><br/>
</div>';
}


function add_zip_archive($current = '', $ext = '', $dir = '')
{
    require_once $GLOBALS['pclzip'];
    
    if($GLOBALS['class'] == 'ftp'){
		$ftp_current = dirname(__FILE__).'/data/GmanagerFtpZip'.time().'.tmp';
    	$ftp_name = dirname(__FILE__).'/data/GmanagerFtpZip'.time().'/';
    	mkdir($ftp_name, 0777);

    	file_put_contents($ftp_current, $GLOBALS['mode']->file_get_contents($current));
    	$tmp = array();
    	foreach($ext as $v){
    		$b = basename($v);
    		$tmp[] = $ftp_name . $b;
    		file_put_contents($ftp_name . $b, $GLOBALS['mode']->file_get_contents($v));
   		}
   		$ext = $tmp;
   		unset($tmp);
   	}

    $zip = new PclZip($GLOBALS['class'] == 'ftp' ? $ftp_current : $current);
    $add = $zip->add($ext, PCLZIP_OPT_ADD_PATH, $dir, PCLZIP_OPT_REMOVE_ALL_PATH);

    if($GLOBALS['class'] == 'ftp'){
    	$GLOBALS['mode']->file_put_contents($current, file_get_contents($ftp_current));
    	unlink($ftp_current);
    	clean($ftp_name);
   	}

    if ($add) {
        return report($GLOBALS['lng']['add_archive_true'], false);
    } else {
    	return report($GLOBALS['lng']['add_archive_false'] . '<br/>' . $zip->errorInfo(true), true);
    }
}


function add_tar_archive($current = '', $ext = '', $dir = '')
{
    require_once $GLOBALS['tar'];

    if($GLOBALS['class'] == 'ftp'){
		$ftp_current = dirname(__FILE__).'/data/GmanagerFtpTar'.time().'.tmp';
    	$ftp_name = dirname(__FILE__).'/data/GmanagerFtpTar'.time().'/';
    	mkdir($ftp_name, 0777);

    	file_put_contents($ftp_current, $GLOBALS['mode']->file_get_contents($current));
    	$tmp = array();
    	foreach($ext as $v){
    		$b = basename($v);
    		$tmp[] = $ftp_name . $b;
    		file_put_contents($ftp_name . $b, $GLOBALS['mode']->file_get_contents($v));
   		}
   		$ext = $tmp;
   		unset($tmp);
   	}

    $tar = new Archive_Tar($GLOBALS['class'] == 'ftp' ? $ftp_current : $current);

    foreach ($ext as $v) {
        $add = $tar->addModify($v, $dir, dirname($v));
    }

    if($GLOBALS['class'] == 'ftp'){
    	$GLOBALS['mode']->file_put_contents($current, file_get_contents($ftp_current));
    	unlink($ftp_current);
    	clean($ftp_name);
   	}

    if ($add) {
        return report($GLOBALS['lng']['add_archive_true'], false);
    } else {
    	return report($GLOBALS['lng']['add_archive_false'], true);
    }
}


function create_zip_archive($name = '', $chmod = '0644', $ext = array(), $comment = '')
{
    require_once $GLOBALS['pclzip'];

    define('CUR', str_replace('//', '/', '/' . strstr($GLOBALS['current'], '/')));

	create_dir(iconv_substr($name, 0, strrpos($name, '/')));

	if($GLOBALS['class'] == 'ftp'){
 		$ftp_name = dirname(__FILE__).'/data/GmanagerFtpZip'.time().'.tmp';
 		$ftp = array();
 		foreach($ext as $f){
 			$ftp[] = $tmp = dirname(__FILE__).'/data/'.basename($f);
 			file_put_contents($tmp, $GLOBALS['mode']->file_get_contents($f));
		}
		$ext = $ftp;
		unset($ftp);
	}

    $zip = new PclZip($GLOBALS['class'] == 'ftp' ? $ftp_name : $name);
    function cb($p_event, &$p_header)
    {
        $test = explode(CUR, $p_header['filename']);
        $p_header['stored_filename'] = ($test[1] ? $test[1] : basename($p_header['filename']));
        return 1;
    }

	if($comment != ''){
		$zip->create($ext, PCLZIP_OPT_COMMENT, $comment, PCLZIP_CB_PRE_ADD, 'cb');
	}
	else{
		$zip->create($ext, PCLZIP_CB_PRE_ADD, 'cb');
	}

	$err = false;
	if($GLOBALS['class'] == 'ftp'){
		if(!$GLOBALS['mode']->file_put_contents($name, file_get_contents($ftp_name))){
			$err = error();
		}
		unlink($ftp_name);
		foreach($ext as $f){
			unlink($f);
		}
	}
	
	
    if ($GLOBALS['mode']->is_file($name)) {
        if ($chmod) {
            rechmod($name, $chmod);
        }
        return report($GLOBALS['lng']['create_archive_true'], false);
    } else {
        return report($GLOBALS['lng']['create_archive_false'] . ($err ? '<br/>'.$err : ''), true);
    }
}


function gz($c = '')
{
    $ext = implode('', gzfile($GLOBALS['class'] == 'ftp' ? ftp_archive_start($c) : $c));
    $gz = explode(chr(0), $GLOBALS['mode']->file_get_contents($c));

	if(!isset($gz[1]) || $gz[1] == ''){
		$gz[1] = basename($c, '.gz');
	}

	if($GLOBALS['class'] == 'ftp'){
		ftp_archive_end();
	}

    if ($ext) {
    	if(!$len = iconv_strlen($ext)){
    		$len = strlen($ext);
   		}
    	return report($GLOBALS['lng']['name'] . ': ' . htmlspecialchars($gz[1], ENT_NOQUOTES) . '<br/>' . $GLOBALS['lng']['archive_size'] . ': ' . format_size(size($c)) . '<br/>' . $GLOBALS['lng']['real_size'] . ': ' . format_size($len) . '<br/>' . $GLOBALS['lng']['archive_date'] . ': ' . strftime($GLOBALS['date_format'], $GLOBALS['mode']->filemtime($c)), false) . archive_fl(trim($ext));
    } else {
        return report($GLOBALS['lng']['archive_error'], true);
    }
}


function gz_extract($c = '', $name = '', $chmod = array())
{
	create_dir($name, $chmod[1]);

	$tmp = ($GLOBALS['class'] == 'ftp' ? ftp_archive_start($c) : $c);

	if(ob_start()){
		readgzfile($tmp);
		$get = ob_get_contents();
		ob_end_clean();
	}
	else{
		$gz = gzopen($tmp, 'r');
		$get = gzread($gz, 2147483647);
		gzclose($gz);
	}

    if($GLOBALS['class'] == 'ftp'){
    	ftp_archive_end();
   	}


    $gz = explode(chr(0), $GLOBALS['mode']->file_get_contents($c));
    if (!isset($gz[1]) || $gz[1] == '') {
        $gz[1] = basename($c, '.gz');
    }


    if(!$GLOBALS['mode']->file_put_contents($name . '/' . $gz[1], $get)){
    	$error = error();
    	return report($GLOBALS['lng']['extract_file_false'] . '<br/>' . $error, true);
   	}

    if ($GLOBALS['mode']->is_file($name . '/' . $gz[1])) {
        if ($chmod[0]) {
            rechmod($name, $chmod[0]);
        }
        return report($GLOBALS['lng']['extract_file_true'], false);
    } else {
        return report($GLOBALS['lng']['extract_file_false'], true);
    }
}


function archive_fl($fl = '')
{
    if (substr_count($fl, "\r") > 2) {
        $arr = explode("\r", xhtml_highlight(str_replace("\n", '', $fl)));
    } else {
        $arr = explode('<br />', xhtml_highlight($fl));
    }

	$page = '';
    foreach ($arr as $i => $val) {
        $page .= '<span class="true_code">' . ($i + 1) . '</span> ' . $val . '<br/>';
    }

    return '<div class="code">' . $page . '</div>';
}


function get_archive_file($archive = '', $f = '')
{
    switch (is_archive(get_type($archive))) {
        case 'ZIP':
            require_once $GLOBALS['pclzip'];
            $zip = new PclZip($GLOBALS['class'] == 'ftp' ? ftp_archive_start($archive) : $archive);
            $ext = $zip->extract(PCLZIP_OPT_BY_NAME, $f, PCLZIP_OPT_EXTRACT_AS_STRING);

            if($GLOBALS['class'] == 'ftp'){
            	ftp_archive_end('');
           	}

            return $ext[0]['content'];
		break;


        case 'TAR':
            require_once $GLOBALS['tar'];
            $tar = new Archive_Tar($archive);
            return $tar->extractInString($f);
		break;
    }
}


function upload_files($tmp = '', $name = '', $dir = '', $chmod = '0644')
{
    if (substr($dir, -1) != '/') {
        $name = basename($dir);
        $dir = dirname($dir) . '/';
    }

    if ($GLOBALS['mode']->file_put_contents($dir . $name, file_get_contents($tmp))) {
        if ($chmod) {
            rechmod($dir . $name, $chmod);
        }
        unlink($tmp);
        return report($GLOBALS['lng']['upload_true'], false);
    } else {
    	$error = error();
    	unlink($tmp);
        return report($GLOBALS['lng']['upload_false'] . '<br/>' . $error, true);
    }
}


function upload_url($url = '', $name = '', $chmod = '0644', $headers = '')
{
    $tmp = array();
    $url = trim($url);

    if (strpos($url, "\n") !== false) {
        foreach (explode("\n", $url) as $v) {
            $v = trim($v);
            $tmp[] = array($v, $name . basename($v));
        }
    } else {
        if (substr($name, -1) != '/') {
            $name = dirname($name) . '/' . basename($name);
        } else {
            $name = $name . basename($url);
        }
        $tmp[] = array($url, $name);
    }

    ini_set('user_agent', str_ireplace('User-Agent:', '', trim($headers)));

    $out = '';
    foreach ($tmp as $v) {
        if ($GLOBALS['mode']->copy($v[0], $v[1], $chmod)) {
            $out .= report($GLOBALS['lng']['upload_true'] . ' (' . $v[0] . ' &gt; ' . $v[1] . ')', false);
        } else {
        	$error = error();
            $out .= report($GLOBALS['lng']['upload_false'] . ' (' . $v[0] . ' x ' . $v[1] . ')<br/>' . $error, true);
        }
    }

    return $out;
}


function send_mail($theme = '', $mess = '', $to = '', $from = '')
{
    if (mail($to, '=?utf-8?B?' . base64_encode($theme) . '?=', $mess, 'From: '.$from."\r\nContent-type: text/plain; charset=utf-8;\r\nX-Mailer: Gmanager ".$GLOBALS['version']."\r\nX-Priority: 3")) {
        return report($GLOBALS['lng']['send_mail_true'], false);
    } else {
    	$error = error();
        return report($GLOBALS['lng']['send_mail_false'] . '<br/>' . $error, true);
    }
}


function show_eval($eval = '')
{

    if (ob_start()) {
    	$info['time'] = microtime(true);
    	$info['ram'] = memory_get_usage(false);
        eval($eval);
        $info['time'] = round(microtime(true) - $info['time'], 6);
        $info['ram'] = format_size(memory_get_usage(false) - $info['ram'], 6);
        $buf = ob_get_contents();
        ob_end_clean();

        $rows = sizeof(explode("\n", $buf)) + 1;
        if ($rows < 3) {
            $rows = 3;
        }
        return '<div class="input">' . $GLOBALS['lng']['result'] . '<br/><textarea cols="48" rows="' . $rows . '">' . htmlspecialchars($buf, ENT_NOQUOTES) . '</textarea><br/>' . str_replace('%time%', $info['time'], $GLOBALS['lng']['microtime']) . '<br/>' . $GLOBALS['lng']['memory_get_usage'] . ' '.$info['ram'].'<br/></div>';
    } else {
        echo '<div class="input">' . $GLOBALS['lng']['result'] . '<pre class="code"><code>';

       	$info['time'] = microtime(true);
    	$info['ram'] = memory_get_usage(false);

        eval($eval);

        $info['time'] = round(microtime(true) - $info['time'], 6);
        $info['ram'] = format_size(memory_get_usage(false) - $info['ram'], 6);

        echo '</code></pre>';
        echo str_replace('%time%', $info['time'], $GLOBALS['lng']['microtime']) . '<br/>' . $GLOBALS['lng']['memory_get_usage'] . ' '.$info['ram'].'<br/></div>';
    }
}


function show_cmd($cmd = '')
{
$buf = '';

/*
	$h = popen($cmd, 'r');
	while (!feof($h)) {
   		$buf .= fgets($h, 4096);
	}
	pclose($h);
*/

if($h = proc_open($cmd, array(array('pipe', 'r'), array('pipe', 'w')), $pipes)){

	//fwrite($pipes[0], '');
    fclose($pipes[0]);

    $buf = stream_get_contents($pipes[1]);
    fclose($pipes[1]);

    proc_close($h);

	$rows = sizeof(explode("\n", $buf)) + 1;
	if ($rows < 3) {
		$rows = 3;
	}

	if(iconv('UTF-8', 'UTF-8', $buf) != $buf){
		$buf = iconv('CP866', 'UTF-8', $buf);
	}
}
else{
	return '<div class="red">' . $GLOBALS['lng']['cmd_error'] . '<br/></div>';
}
	return '<div class="input">' . $GLOBALS['lng']['result'] . '<br/><textarea cols="48" rows="' . $rows . '">' . htmlspecialchars($buf, ENT_NOQUOTES) . '</textarea></div>';
}


function replace($current = '', $from = '', $to = '', $regexp = '')
{
    if (!$from) {
        return report($GLOBALS['lng']['replace_false_str'], true);
    }
    $c = $GLOBALS['mode']->file_get_contents($current);

    if ($regexp) {
        preg_match_all('/' . str_replace('/', '\/', $from) . '/', $c, $all);
        $all = sizeof($all[0]);
        if (!$all) {
            return report($GLOBALS['lng']['replace_false_str'], true);
        }
        $str = preg_replace('/' . str_replace('/', '\/', $from) . '/', $to, $c);
        if ($str) {
            if(!$GLOBALS['mode']->file_put_contents($current, $str)){
            	$error = error();
            	return report($GLOBALS['lng']['replace_false_file'] . '<br/>' . $error, true);
           	}
        } else {
            return report($GLOBALS['lng']['regexp_error'], true);
        }
    } else {
        $all = substr_count($c, $from);
        if (!$all) {
            return report($GLOBALS['lng']['replace_false_str'], true);
        }


        if(!$GLOBALS['mode']->file_put_contents($current, str_replace($from, $to, $c))){
       		$error = error();
            return report($GLOBALS['lng']['replace_false_file'] . '<br/>' . $error, true);
       	}
       	
       	$str = true;
    }

    if ($str) {
        return report($GLOBALS['lng']['replace_true'] . $all, false);
    } else {
        return report($GLOBALS['lng']['replace_false_file'], true);
    }
}


function zip_replace($current = '', $f = '', $from = '', $to = '', $regexp = '')
{
    if (!$from) {
        return report($GLOBALS['lng']['replace_false_str'], true);
    }

    $c = edit_zip_file($current, $f);
    $c = $c['text'];

    if ($regexp) {
        preg_match_all('/' . str_replace('/', '\/', $from) . '/', $c, $all);
        $all = sizeof($all[0]);
        if (!$all) {
            return report($GLOBALS['lng']['replace_false_str'], true);
        }
        $str = preg_replace('/' . str_replace('/', '\/', $from) . '/', $to, $c);
        if ($str) {
            return edit_zip_file_ok($current, $f, $str);
        } else {
            return report($GLOBALS['lng']['regexp_error'], true);
        }
    } else {
        $all = substr_count($c, $from);
        if (!$all) {
            return report($GLOBALS['lng']['replace_false_str'], true);
        }

        return edit_zip_file_ok($current, $f, str_replace($from, $to, $c));
    }
}


function search($c = '', $s = '', $w = '', $r = '')
{
    if ($GLOBALS['target']) {
        $target = ' target="_blank"';
    } else {
        $target = '';
    }
	
    $c = str_replace('//', '/', $c . '/');

    $i = 0;
    $in = '';
    $page = array();

    foreach($GLOBALS['mode']->iterator($c) as $f) {
        if ($GLOBALS['mode']->is_dir($c . $f)) {
            search($c . $f . '/', $s, $w, $r);
            continue;
        }

		//$h_file = htmlspecialchars($c . $f, ENT_COMPAT);
		$r_file = str_replace('%2F', '/', rawurlencode($c . $f));
		$type = htmlspecialchars(get_type($f), ENT_NOQUOTES);
		$archive = is_archive($type);

        $time = $GLOBALS['mode']->filemtime($c . $f);
        $name = htmlspecialchars(str_link($c . $f), ENT_NOQUOTES);
        
        if ($r) {
            $s = strtolower($s);
            $f = strtolower($f);
        }

        if (!$w) {
            if (iconv_strpos($f, $s) === false) {
                continue;
            }
        } else {

            if ($type == 'GZ') {
            	if(ob_start()){
            		readgzfile($c . $f);
            		$fl = ob_get_contents();
					ob_end_clean();
				}
				else{
					$gz = gzopen($c . $f, 'r');
					$fl = gzread($gz, 2147483647);
					gzclose($gz);
				}
            } else {
                $fl = $GLOBALS['mode']->file_get_contents($c . $f);
            }

            if ($r) {
                $fl = strtolower($fl);
            }

            if (!$in = substr_count($fl, $s)) {
                continue;
            }
            $in = ' (' . $in . ')';
        }


        $i++;
        $pname = $pdown = $ptype = $psize = $pchange = $pdel = $pchmod = $pdate = $pn = '';

        if($GLOBALS['index']['name']){
        	if($archive){
        		$pname = '<td><a href="index.php?' . $r_file . '">' . $name . '</a>' . $in . '</td>';
       		}
       		else{
       			$pname = '<td><a href="edit.php?' . $r_file . '"' . $target . '>' . $name . '</a>' . $in . '</td>';
			}
       	}
       	if($GLOBALS['index']['down']){
        	$pdown = '<td><a href="change.php?get=' . $r_file . '">' . $GLOBALS['lng']['get'] . '</a></td>';
       	}
       	if($GLOBALS['index']['type']){
        	$ptype = '<td>' . $type . '</td>';
       	}
       	if($GLOBALS['index']['size']){
        	$psize = '<td>' . format_size(size($c . $f)) . '</td>';
       	}
       	if($GLOBALS['index']['change']){
        	$pchange = '<td><a href="change.php?' . $r_file . '">' . $GLOBALS['lng']['ch'] . '</a></td>';
       	}
       	if($GLOBALS['index']['del']){
        	$pdel = '<td><a'.($GLOBALS['del_notify'] ? ' onclick="return confirm(\''.$GLOBALS['lng']['del_notify'].'\')"' : '').' href="change.php?go=del&amp;c=' . $r_file . '">' . $GLOBALS['lng']['dl'] . '</a></td>';
       	}
       	if($GLOBALS['index']['chmod']){
        	$pchmod = '<td><a href="change.php?go=chmod&amp;c=' . $r_file . '">' . look_chmod($c . $f) . '</a></td>';
       	}
       	if($GLOBALS['index']['date']){
        	$pdate = '<td>' . strftime($GLOBALS['date_format'], $time) . '</td>';
       	}
       	if($GLOBALS['index']['n']){
       		$pn = '<td>' . $i . '</td>';
 		}
       	
		$page[$f] = '<td><input name="check[]" type="checkbox" value="' . $r_file . '"/></td>' . $pname . $pdown . $ptype . $psize . $pchange . $pdel . $pchmod . $pdate . $pn;

    }

    natcasesort($page);

    $line = false;
    foreach ($page as $var) {
   		$line = !$line;
   		if($line){
  			echo '<tr class="border">' . $var . '</tr>';
		}
		else{
   			echo '<tr class="border2">' . $var . '</tr>';
		}
	}

}


function fname($f = '', $name = '', $register = '', $i = '')
{
    // [n=0] - meter
    // [f] - type
    // [name] - name
    // [date] - date
	
	// $f = rawurldecode($f);

    $info = pathinfo($f);
	if(preg_match_all('/\[n=*(\d*)\]/U', $name, $arr, PREG_SET_ORDER)){
		foreach($arr as $var){
    		$name = str_replace($var[0], $var[1] + $i, $name);
   		}
   	}
    //$name = str_replace('[n]', $i, $name);
    $name = str_replace('[f]', $info['extension'], $name);
    $name = str_replace('[name]', $info['filename'], $name);
    $name = str_replace('[date]', strftime('%d_%m_%Y'/*, time()*/), $name);


    if ($register == 1) {
        $tmp = strtolower($name);
        if(!iconv_strlen($tmp)){
        	$tmp = iconv('Windows-1251', 'UTF-8', strtolower(iconv('UTF-8', 'Windows-1251', $name)));
        }
    } elseif ($register == 2) {
        $tmp = strtoupper($name);
        if(!iconv_strlen($tmp)){
        	$tmp = iconv('Windows-1251', 'UTF-8', strtoupper(iconv('UTF-8', 'Windows-1251', $name)));
        }
    }
    else{
    	$tmp = $name;
   	}

    if ($GLOBALS['mode']->rename($f, $info['dirname'] . '/' . $tmp)) {
        return report($info['basename'] . ' - ' . $tmp, false);
    } else {
    	$error = error();
        return report($info['basename'] . ' - ' . $tmp . ' (' . $error . ')', true);
    }
}


function sql_parser($sql = '')
{
    $arr = explode("\n", $sql);

    for ($i = 0, $size = sizeof($arr); $i <= $size; ++$i) {
        if (trim($arr[$i]) && $arr[$i][0] != '#' && $arr[$i][0] . $arr[$i][1] != '--') {
            $str .= $arr[$i];
        }
    }

    //$str = "SET sql_mode = 'IGNORE_SPACE';\n".$str;

    $str = trim(preg_replace('/;[\s+](EXPLAIN|SELECT|ALTER|CREATE|INSERT|DELETE|UPDATE|DROP|OPTIMIZE|ANALYZE|RESTORE|CHECKSUM|CHECK\s+TABLE|BACKUP\s+TABLE|REPAIR|TRUNCATE|REPLACE|SHOW|SET|USE|LOAD\s+DATA|RENAME\s+TABLE|EXECUTE|DEALLOCATE|DESCRIBE|LOCK\s+TABLES|START\s+TRANSACTION|PREPARE|CALL|HANDLER|SAVEPOINT|HELP|GRANT|REVOKE|DO)\s+/i', ";\n$1 ", $str));
    return preg_split('/;[\t\r\n]+/i', $str);
}


function sql_installer($host = '', $name = '', $pass = '', $db = '', $charset = '', $sql = '')
{

    if (!$sql) {
        return;
    }

    if (!$query = sql_parser($sql)) {
        return;
    }

    $php = '<?php
// SQL Installer
// Created in Gmanager ' . $GLOBALS['version'] . '
// http://wapinet.ru/gmanager/

error_reporting(0);

if(strpos($_SERVER[\'HTTP_USER_AGENT\'], \'MSIE\') !== false){
	header(\'Content-type: text/html; charset=UTF-8\');
}
else{
	header(\'Content-type: application/xhtml+xml; charset=UTF-8\');
}

echo \'<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ru">
<head>
<title>SQL Installer</title>
<style type="text/css">
body {
	background-color: #cccccc;
	color: #000000;
}
</style>
</head>
<body>
<div>\';


if(!$_POST){
echo \'<form action="\'.$_SERVER[\'PHP_SELF\'].\'" method="post">
<div>
' . $GLOBALS['lng']['mysql_user'] . '<br/>
<input type="text" name="name" value="' . $name . '"/><br/>
' . $GLOBALS['lng']['mysql_pass'] . '<br/>
<input type="text" name="pass" value="' . $pass . '"/><br/>
' . $GLOBALS['lng']['mysql_host'] . '<br/>
<input type="text" name="host" value="' . $host . '"/><br/>
' . $GLOBALS['lng']['mysql_db'] . '<br/>
<input type="text" name="db" value="' . $db . '"/><br/>
<input type="submit" value="' . $GLOBALS['lng']['install'] . '"/>
</div>
</form>
</div></body></html>\';
exit;
}

$connect = mysql_connect($_POST[\'host\'], $_POST[\'name\'], $_POST[\'pass\']) or die (\'Can not connect to MySQL</div></body></html>\');
mysql_select_db($_POST[\'db\'], $connect) or die (\'Error select the database</div></body></html>\');
mysql_query(\'SET NAMES `' . str_ireplace('utf-8', 'utf8', $charset) . '`\', $connect);' . "\n\n";

    foreach ($query as $q) {
        $php .= '$sql = "' . str_replace('"', '\"', trim($q)) . ';";
mysql_query($sql, $connect);
if($err = mysql_error($connect)){
	$error[] = $err."\n SQL:\n".$sql;
}' . "\n\n";
    }

    $php .= 'if($error){
	echo \'Error:<pre>\'.htmlspecialchars(print_r($error, true), ENT_NOQUOTES).\'</pre>\';
}else{
	echo \'Ok\';
}

echo \'</div></body></html>\'
?>';

    return $php;
}


function sql($name = '', $pass = '', $host = '', $db = '', $data = '', $charset = '')
{

    if (!$connect = mysql_connect($host, $name, $pass)) {
        return report($GLOBALS['lng']['mysq_connect_false'], true);
    }
    if ($charset) {
        mysql_query('SET NAMES `' . str_ireplace('utf-8', 'utf8', $charset) . '`', $connect);
    }

    if ($db) {
        if (!mysql_select_db($db, $connect)) {
            return report($GLOBALS['lng']['mysq_select_db_false'], true);
        }
    }


    $i = $time = 0;
    $out = '';
    foreach (sql_parser($data) as $q) {
        $result = array();
        $str = '';


		while(iconv_substr($q, iconv_strlen($q)-1, 1) == ';'){
			$q = iconv_substr($q, 0, -1);
		}

		$start = microtime(true);
			$r = mysql_query($q . ';', $connect);
		$time += microtime(true) - $start;

        if (!$r) {
            return report($GLOBALS['lng']['mysq_query_false'], true) . '<div><code>' . mysql_error($connect) . '</code></div>';
        } else {
            while ($arr = mysql_fetch_assoc($r)) {
                if ($arr && $arr !== true) {
                    $result[] = $arr;
                }
            }
        }
        $i++;
		
		if($result){
			$str .= '<tr><th> '.implode(' </th><th> ', array_map('htmlspecialchars', array_keys($result[0]))).' </th></tr>';

        	foreach ($result as $v) {
            	$str .= '<tr class="border">';
            	foreach ($v as $value) {
                	$str .= '<td><pre style="margin:0;"><a href="#sql" onclick="paste(\'' . rawurlencode($value) . '\');">' . htmlspecialchars($value, ENT_NOQUOTES) . '</a></pre></td>';
            	}
            	$str .= '</tr>';
        	}

            $out .= '<table class="telo">' . $str . '</table>';
		}
    }

    mysql_close($connect);
    return report($GLOBALS['lng']['mysql_true'] . $i . '<br/>' . str_replace('%time%', round($time, 6), $GLOBALS['lng']['microtime']), false) . $out;
}


function go($pg = 0, $all = 0, $text = '')
{
    $go = '';

    $page1 = $pg - 2;
    $page2 = $pg - 1;
    $page3 = $pg + 1;
    $page4 = $pg + 2;

    if ($page1 > 0) {
        $go .= '<a href="' . $_SERVER['PHP_SELF'] . '?pg=' . $page1 . $text . '">' . $page1 . '</a> ';
    }

    if ($page2 > 0) {
        $go .= '<a href="' . $_SERVER['PHP_SELF'] . '?pg=' . $page2 . $text . '">' . $page2 . '</a> ';
    }

    $go .= $pg . ' ';

    if ($page3 <= $all) {
        $go .= '<a href="' . $_SERVER['PHP_SELF'] . '?pg=' . $page3 . $text . '">' . $page3 . '</a> ';
    }
    if ($page4 <= $all) {
        $go .= '<a href="' . $_SERVER['PHP_SELF'] . '?pg=' . $page4 . $text . '">' . $page4 . '</a> ';
    }

    if ($all > 3 && $all > $page4) {
        $go .= '... <a href="' . $_SERVER['PHP_SELF'] . '?pg=' . $all . $text . '">' . $all . '</a>';
    }

    if ($page1 > 1) {
        $go = '<a href="' . $_SERVER['PHP_SELF'] . '?pg=1' . $text . '">1</a> ... ' . $go;
    }

    if ($go != $pg . ' ') {
        return '<tr><td class="border" colspan="' . (array_sum($GLOBALS['index']) + 1) . '">&#160;' . $go . '</td></tr>';
    }
}


function str_link($str = '')
{
    $tmp['strlen'] = iconv_strlen($str);
    $tmp['tmp'] = intval($GLOBALS['link'] / 2);
    $tmp['start'] = $tmp['tmp'] + 2;

    if ($tmp['strlen'] > $GLOBALS['link']) {
        return iconv_substr($str, 0, $tmp['start']) . ' ... ' . iconv_substr($str, ($tmp['strlen'] - $tmp['start']));
    }

    return $str;
}


// Содержимое файла, имя файла, аттач (опционально), MIME (опционально)
function getf($f = '', $name = '', $attach = '', $mime = '')
{
    ob_implicit_flush(1);
    set_time_limit(9999);

    ini_set('zlib.output_compression', 0);
    ini_set('output_handler', '');

    //iconv_set_encoding('internal_encoding', 'windows-1251');

    // Длина файла
    $sz = $len = strlen($f);


    $out = $f;

    // "От" и  "До" по умолчанию
    $file_range = array(
		'from' => 0,
		'to' => $len
	);

    // Если докачка
    if ($range = isset($_SERVER['HTTP_RANGE'])) {
        if (preg_match('/bytes=(\d+)-(\d*)/i', $_SERVER['HTTP_RANGE'], $matches)) {
            // "От", "До" если "До" нету, "До" равняется размеру файла
            $file_range = array('from' => $matches[1], 'to' => (!$matches[2]) ? $len : $matches[2]);
            // Режем переменную в соответствии с данными
            if ($file_range) {
                $out = substr($out, $file_range['from'], $file_range['to']);
                $sz = $file_range['to'] - $file_range['from'];
            }
        }
    }


    // Заголовки...
    if ($file_range['from']) {
        header('HTTP/1.0 206 Partial Content');
    } else {
        header('HTTP/1.0 200 OK');
    }

    // Ставим MIME в зависимости от расширения
    if (!$mime) {
        $info = pathinfo($name);

        switch (strtolower($info['extension'])) {
            default:
                $mime = 'application/octet-stream';
                break;

            case 'jar':
                $mime = 'application/java-archive';
                break;

            case 'jad':
                $mime = 'text/vnd.sun.j2me.app-descriptor';
                break;

            case 'cab':
                $mime = 'application/vnd.ms-cab-compressed';
                break;

            case 'sis':
                $mime = 'application/vnd.symbian.install';
                break;

            case 'zip':
                $mime = 'application/x-zip';
                break;

            case 'rar':
                $mime = 'application/x-rar-compressed';
                break;

            case '7z':
                $mime = 'application/x-7z-compressed';
                break;

            case 'gz':
            case 'tgz':
                $mime = 'application/x-gzip';
                break;

            case 'bz':
            case 'bz2':
                $mime = 'application/x-bzip';
                break;

            case 'jpg':
            case 'jpe':
            case 'jpeg':
                $mime = 'image/jpeg';
                break;

            case 'gif':
                $mime = 'image/gif';
                break;

            case 'png':
                $mime = 'image/png';
                break;

            case 'txt':
            case 'dat':
            case 'php':
            case 'php4':
            case 'php5':
            case 'phtml':
            case 'htm':
            case 'html':
            case 'shtm':
            case 'shtml':
            case 'wml':
            case 'css':
            case 'js':
            case 'xml':
            case 'sql':
                $mime = 'text/plain';
                break;

            case 'mmf':
                $mime = 'application/x-smaf';
                break;

            case 'mid':
                $mime = 'audio/mid';
                break;

            case 'mp3':
                $mime = 'audio/mpeg';
                break;

            case 'amr':
                $mime = 'audio/amr';
                break;

            case 'wav':
                $mime = 'audio/x-wav';
                break;

            case 'mp4':
                $mime = 'video/mp4';
                break;

            case 'wmv':
                $mime = 'video/x-ms-wmv';
                break;

            case '3gp':
                $mime = 'video/3gpp';
                break;

            case 'avi':
                $mime = 'video/x-msvideo';
                break;

            case 'mpg':
            case 'mpe':
            case 'mpeg':
                $mime = 'video/mpeg';
                break;

            case 'pdf':
                $mime = 'application/pdf';
                break;

            case 'doc':
            case 'docx':
            	$mime = 'application/msword';
            	break;

           	case 'swf':
           		$mime = 'application/x-shockwave-flash';
           		break;
        }
    }


    //header('Date: '.gmdate('r', time()));
    //header('Content-Transfer-Encoding: binary');
    //header('Last-Modified: '.gmdate('r', 1234));

    // Кэш
    header('Cache-Control: public, must-revalidate, max-age=0');
    header('Pragma: cache');

    // Хэш
    $etag = md5($f);
    $etag = substr($etag, 0, 4) . '-' . substr($etag, 5, 5) . '-' . substr($etag, 10, 8);
    header('ETag: "' . $etag . '"');


    //header('Connection: Close');
    header('Keep-Alive: timeout=15, max=50');
    header('Connection: Keep-Alive');

    header('Accept-Ranges: bytes');
    header('Content-Length: ' . $sz);


    // Если докачка
    if ($range) {
        header('Content-Range: bytes ' . $file_range['from'] . '-' . $file_range['to'] . '/' . $len);
    }


    // Если отдаем как аттач
    if ($attach) {
        header('Content-Type: ' . $mime);
        header('Content-Disposition: attachment; filename="' . $name . '"');
    } elseif ($mime == 'text/plain') {
    	// header('Content-Type: text/plain; charset=' . $charset);
        header('Content-Type: text/plain;');
    } else {
        header('Content-Type: ' . $mime);
    }
    //ob_end_flush();
	
	exit($out);
}



function getData($url = '', $headers = '')
{

$u = parse_url($url);

$host = $u['host'];
$path = isset($u['path']) ? $u['path'] : '/';
$port = isset($u['port']) ? $u['port'] : 80;

if(isset($u['query'])){
	$path .= '?'.$u['query'];
}
if(isset($u['fragment'])){
	$path .= '#'.$u['fragment'];
}

	$fp	= fsockopen($host, $port, $errno, $errstr, 10);
	if(!$fp){
		return false;
	}
	else {
		$out = 'GET ' . $path . ' HTTP/1.0' . "\r\n";
		$out .= 'Host: ' . $host . "\r\n";
		
		if($headers){
			$out .=	trim($headers) . "\r\n";
		}
		else{
			$out .=	'User-Agent: ' . $_SERVER['HTTP_USER_AGENT'] . "\r\n";
			$out .=	'Accept: ' . $_SERVER['HTTP_ACCEPT'] . "\r\n";
			$out .=	'Accept-Language: ' . $_SERVER['HTTP_ACCEPT_LANGUAGE'] . "\r\n";
    		$out .=	'Accept-Charset: ' . $_SERVER['HTTP_ACCEPT_CHARSET'] . "\r\n";
			//$out .= 'TE: deflate, gzip, chunked, identity, trailers' . "\r\n";
			$out .= 'Connection: Close' . "\r\n";
		}
		$out .= "\r\n";
		
		fwrite($fp, $out);
		$headers = '';
		while($str = trim(fgets($fp, 512))) {
			$headers .= $str . "\r\n";
		}
		$body = '';
		while(!feof($fp)) {
			$body .= fgets($fp, 4096);
		}
		fclose($fp);
	}

	return array (
		'headers' => $headers,
		'body' => $body
		);
}


/**
 * @param void
 * @return string
 */
function error(){
	$error = error_get_last();
	//$message = explode(':', $error['message']);
 	//return 'Error: ' . end($message);
 	return $error['message'];
}


function report($text = '', $error = false){
	if($error){
		return '<div class="red">'.$text.'<br/></div>';
	}
	
	return '<div class="green">'.$text.'<br/></div>';
}


function encoding($text, $charset){
	$ch = explode(' -> ', $charset);
	if($text){
		$text = iconv($ch[0], $ch[1], $text);
	}
	return array(0 => $ch[0], 1 => $ch[1], 'text' => $text);
}


function ftp_move_files($from = '', $to = '', $chmodf = '0644', $chmodd = '0755'){

	$h = opendir($from);
	while(($f = readdir($h)) !== false){
		if($f == '.' || $f == '..'){
			continue;
		}

		if(is_dir($from . '/' . $f)) {
			$GLOBALS['mode']->mkdir($to . '/' . $f, $chmodd);
			ftp_move_files($from . '/' . $f, $to . '/' . $f, $chmodf, $chmodd);
			rmdir($from . '/' . $f);
		}
		else {
			$GLOBALS['mode']->file_put_contents($to . '/' . $f, file_get_contents($from . '/' . $f));
			rechmod($to . '/' . $f, $chmodf);
			unlink($from . '/' . $f);
		}
	}
closedir($h);
rmdir($from);
}


function ftp_archive_start($current = ''){
    	$GLOBALS['ftp_archive_start'] = dirname(__FILE__).'/data/GmanagerFtpArchive'.time().'.tmp';
    	file_put_contents($GLOBALS['ftp_archive_start'], $GLOBALS['mode']->file_get_contents($current));
    	return $GLOBALS['ftp_archive_start'];
}


function ftp_archive_end($current = ''){
		if($current != ''){
			$GLOBALS['mode']->file_put_contents($current, file_get_contents($GLOBALS['ftp_archive_start']));
		}
		unlink($GLOBALS['ftp_archive_start']);
}


function get_type($f){
	$type = array_reverse(explode('.', strtoupper(basename($f))));
	if((isset($type[1]) && $type[1] != '') && ($type[1] . '.' . $type[0] == 'TAR.GZ' || $type[1] . '.' . $type[0] == 'TAR.BZ' || $type[1] . '.' . $type[0] == 'TAR.GZ2' || $type[1] . '.' . $type[0] == 'TAR.BZ2')){
		return $type[1].'.'.$type[0];
	}

	return $type[0];
}


function is_archive($type){
	switch(strtoupper($type)){
		case 'TAR':
		case 'TGZ':
		case 'TGZ2':
		case 'TBZ':
		case 'TBZ2':
		case 'TAR.GZ':
		case 'TAR.GZ2':
		case 'TAR.BZ':
		case 'TAR.BZ2':
		case 'BZ':
		case 'BZ2':
			return 'TAR';
		break;

		case 'ZIP':
		case 'JAR':
		case 'WAR':
		case 'AAR':
			return 'ZIP';
		break;

		case 'GZ':
		case 'GZ2':
			return 'GZ';
		break;

		default:
			return '';
		break;
	}
}


function clean($name = ''){
	$h = opendir($name);
	while(($f = readdir($h)) !== false){
		if($f == '.' || $f == '..'){
			continue;
		}

		if(is_dir($name . '/' . $f)) {
			rmdir($name . '/' . $f);
			clean($name . '/' . $f);
		}
		else {
			unlink($name . '/' . $f);
		}
	}
closedir($h);
rmdir($name);
}
?>