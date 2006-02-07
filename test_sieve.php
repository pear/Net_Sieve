<?php
//
// +----------------------------------------------------------------------+
// | PHP Version 4                                                        |
// +----------------------------------------------------------------------+
// | Copyright (c) 1997-2003 The PHP Group                                |
// +----------------------------------------------------------------------+
// | This source file is subject to version 2.02 of the PHP license,      |
// | that is bundled with this package in the file LICENSE, and is        |
// | available at through the world-wide-web at                           |
// | http://www.php.net/license/2_02.txt.                                 |
// | If you did not receive a copy of the PHP license and are unable to   |
// | obtain it through the world-wide-web, please send a note to          |
// | license@php.net so we can mail you a copy immediately.               |
// +----------------------------------------------------------------------+
// | Author: Damian Alejandro Fernandez Sosa <damlists@cnba.uba.ar>       |
// +----------------------------------------------------------------------+

include_once('Net/Sieve.php');
error_reporting(E_ALL);

$user='user';
$passwd='password';
$host='localhost';
$port="2000";


//you can create a file called passwords.php and store your $user,$pass,$host and $port values in it
// or you can modify this script
@include_once("./passwords.php");




$sieve_script_name1='test script1';
// The script
$sieve_script1="require \"fileinto\";\n\rif header :contains \"From\" \"@cnba.uba.ar\" \n\r{fileinto \"INBOX.Test1\";}\r\nelse \r\n{fileinto \"INBOX\";}";


$sieve_script_name2='test script2';
$sieve_script2="require \"fileinto\";\n\rif header :contains \"From\" \"@cnba.uba.ar\" \n\r{fileinto \"INBOX.Test\";}\r\nelse \r\n{fileinto \"INBOX\";}";


$sieve_script1="require \"vacation\";\nvacation\n:days 7\n:addresses [\"matthew@de-construct.com\"]\n:subject \"This is a test\"\n\"I'm on my holiday!\nsadfafs\";";


//$sieve=new Net_Sieve($user, $passwd, $host , $port , 'PLAIN');
//$sieve=new Net_Sieve($user, $passwd, $host , $port, 'DIGEST-MD5' );
//$sieve=new Net_Sieve($user, $passwd, $host , $port);
$sieve=new Net_Sieve();

$sieve->setDebug(true);

if(PEAR::isError($error = $sieve->connect($host , $port) ) ) {
    echo "  there was an error trying to connect to the server. The error is: " . $error->getMessage() . "\n" ;
    exit();
}



//if(PEAR::isError($error = $sieve->login($user, $passwd  , 'PLAIN' , '', false ) ) ) {
if(PEAR::isError($error = $sieve->login($user, $passwd  , null , '', false ) ) ) {
    echo "  there was an error trying to login to the server. The error is: " . $error->getMessage()  . "\n";
    exit();
}





// I list the scripts that I Have installed
echo "These are the scripts that I have in the server:\n";
print_r($sieve->listScripts());
echo "\n";
exit();

echo "I remove script 1 ($sieve_script_name1)......\n";
if( !PEAR::isError($error = $sieve->removeScript($sieve_script_name1) ) ){
    echo "  script '$sieve_script_name1' removed ok!\n";
}else{
    echo "  there was an error trying to remove the script '$sieve_script_name1'. The error is: " . $error->getMessage()  . "\n";
}
echo "\n";



// I try to delete again de same script, the method must fail

echo "I remove script 1 ($sieve_script_name1)......\n";
if( !PEAR::isError($error = $sieve->removeScript($sieve_script_name1) ) ) {
    echo "  script '$sieve_script_name1' removed ok!\n";
}else{
    echo "  there was an error trying to remove the script '$sieve_script_name1'. The error is: " . $error->getMessage() . "\n" ;
}
echo "\n";





/*
echo "I'll check if the server has space to store '$sieve_script_name1' script .....";
if(!PEAR::isError( $error = $sieve->haveSpace($sieve_script_name1, strlen($sieve_script1)))) {
    echo "  ok! the server has a lot of space!\n";
}else{
    echo "  the server can't store the script. The error is: " . $error->getMessage() . "\n" ;
}
echo "\n";
*/



echo "I install the script '$sieve_script_name1' and mark it active.....\n";
if(!PEAR::isError( $error = $sieve->installScript($sieve_script_name1, $sieve_script1,true))) {
    echo "  script '$sieve_script_name1' installed ok!\n";
}else{
    echo "  there was an error trying to install the script '$sieve_script_name1'. The error is: " . $error->getMessage() . "\n" ;
}
echo "\n";


echo "This is the script I just installed.....\n";
if(!PEAR::isError( $error = $sieve->getScript($sieve_script_name1 ))) {
    echo "  script '$sieve_script_name1':\n$error\n";
}else{
    echo "  there was an error trying to install the script '$sieve_script_name1'. The error is: " . $error->getMessage() . "\n" ;
}




echo "I install the script '$sieve_script_name2' but it is not marked as active.....\n";
if(!PEAR::isError( $error = $sieve->installScript($sieve_script_name2, $sieve_script2))) {
    echo "  script '$sieve_script_name2' installed ok!\n";
}else{
    echo "  there was an error trying to install the script '$sieve_script_name2'. The error is: " . $error->getMessage() . "\n" ;
}
echo "\n";


echo "Now set script 2 as active...\n";
if(!PEAR::isError($error = $sieve->setActive($sieve_script_name2))) {
    echo "  script '$sieve_script_name2' marked as active ok!\n";
}else{
    echo "  there was an error trying to mark as active the script '$sieve_script_name2'. The error is: " . $error->getMessage() . "\n" ;
}
echo "\n";

echo "Now get the active script....\n";
if( !PEAR::isError($error = $script= $sieve->getActive() ) ) {
    echo "the script marked as active is: $script\n";
}else{
    echo "  there was an error trying to get the activescript, the error is:" . $script->getMessage() ;
}
echo "\n";


echo "The server has the following extensions:\n";

$exts=$sieve->getExtensions();

for($i=0 ; $i < count($exts) ; $i++){

        echo sprintf("    %s. %s\n", $i+1 , $exts[$i]);
}
echo "\n";





$ext='pichula';
if($sieve->hasExtension( $ext ) ) {
    echo "this server supports the '$ext' extenssion\n";
} else {
    echo "this server does not supports the '$ext' extenssion\n";
}

echo "\n";



$ext='fileinto';
if($sieve->hasExtension( $ext ) ) {
    echo "this server supports the '$ext' extenssion\n";
} else {
    echo "this server does not supports the '$ext' extenssion\n";
}






echo "\n\nThe server has the following Auth Methods:\n";

$meths=$sieve->getAuthMechs();

for($i=0 ; $i < count($meths) ; $i++){

        echo sprintf("    %s. %s\n", $i+1 , $meths[$i]);
}
echo "\n";





$meth='pichula';
if($sieve->hasAuthMech( $meth ) ){
    echo "this server supports the '$meth' Auth Method\n";
} else {
    echo "this server does not supports the '$meth' Auth Method\n";
}

echo "\n";



$meth='cram-md5';
if($sieve->hasAuthMech( $meth ) ){
    echo "this server supports the '$meth' Auth Method\n";
} else {
    echo "this server does not supports the '$meth' Auth Method\n";
}

echo "\n";




?>