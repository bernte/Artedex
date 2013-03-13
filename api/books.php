<?php

// Register routes
$_mediatheekDomain = 'http://www.arteveldehogeschool.be/bib/cgi-bin/';
$_urlMediatheekBidocsru = $_mediatheekDomain . 'bidocsru.exe';
$_urlMediatheekBidocws = $_mediatheekDomain . 'bidocws.exe';
$_mediatheekBib = "MAR";
$_mediatheekSearchStartindex = 1;
$_mediatheekSearchEndindex = 10;
$_mediatheekSearchString = "javascript";
$_mediatheekSearchField = "ta";

$app->post('/mediatheekitems', function() use ($app, $_urlMediatheekBidocsru, $_mediatheekBib, $_mediatheekSearchStartindex, $_mediatheekSearchEndindex, $_mediatheekSearchString, $_mediatheekSearchField){
try {
//GET SEARCH VIA POST VARIABLES
$requestBody = $app->request()->getBody();
$search = json_decode($requestBody, true);
//FULL URL
$_mediatheekSearchStartindex = 1+($search["currentpage"]-1)*$search["amountperpage"];
$_mediatheekSearchEndindex = $_mediatheekSearchStartindex+$search["amountperpage"];
$_mediatheekSearchString = $search["searchstring"];
$_mediatheekSearchField = $search["searchfield"];
$fullurl = $_urlMediatheekBidocsru . '?' . 'bib=' . $_mediatheekBib . '&' . 'act=sru' . '&' . 'veld=' . $_mediatheekSearchField . '&' . 'sort=uv' . '&' . 'start=' . '&' . 'start=' . $_mediatheekSearchStartindex . '&' . 'max=' . $_mediatheekSearchEndindex . '&' . 'zoek=' . $_mediatheekSearchString;
//DO SOME INTERNET TRAFFIC
$content = file_get_contents($fullurl);
//CREATE XML FROM CONTENTS
$xml = simplexml_load_string($content);
$xml = new SimpleXMLElement($xml->asXML());
$xml->registerXPathNamespace('r', 'http://www.loc.gov/MARC21/slim');
//CREATE JSON FROM XML
$json = '{';
$json .= '"paging":{' ;
$json .= '"resulttotal":' . ($xml->resulttotal);
$json .= ',"currentpage":' . $search["currentpage"];
$json .= ',"amountperpage":' . $search["amountperpage"];
$json .= ',"searchstring":"' . $_mediatheekSearchString + '"';
$json .= '}';
$json .= ',';
$json .= '"results":[';
$records = $xml->xpath('//response/result/resultrecord/r:record');
$firstitem = true;
foreach ($records as $record){
if(!$firstitem)
$json .= ',';
else
$firstitem = false;
//CREATE A NEW MEDIATHEEK ITEM
$mediatheekItem = new MediatheekItem();

//KNOW THE NAMESPACE
$record->registerXPathNamespace('r', 'http://www.loc.gov/MARC21/slim');

//TITEL EN TYPE
$datafield = $record->xpath('r:datafield[@tag="245"]');
if(count($datafield) > 0){
$datafield[0]->registerXPathNamespace('r', 'http://www.loc.gov/MARC21/slim');
$subfields = $datafield[0]->subfield;

foreach($subfields as $subfield){
$code = $subfield->attributes()->code;
switch($code){
case 'a':
$mediatheekItem->titel = '' . $subfield;
break;
case 'h':
$mediatheekItem->type = '' . $subfield;
break;
default:
break;
}
}
}

//ID
$controlfield = $record->xpath('r:controlfield[@tag="001"]');
if(count($controlfield) > 0){
$controlfield[0]->registerXPathNamespace('r', 'http://www.loc.gov/MARC21/slim');
$mediatheekItem->id = '' . $controlfield[0];
}

//AANWINSTNUMMER + PLAATS
$datafield = $record->xpath('r:datafield[@tag="876"]');
if(count($datafield) > 0){
$datafield[0]->registerXPathNamespace('r', 'http://www.loc.gov/MARC21/slim');
$subfields = $datafield[0]->subfield;

$p;
$c;

foreach($subfields as $subfield){
$code = $subfield->attributes()->code;
switch($code){
case 'p':
$p = $subfield;
break;
case 'c':
$c = $subfield;
break;
case 'l':
$mediatheekItem->plaats = '' . $subfield;
break;
default:
break;
}
}
$mediatheekItem->aanwinstnummer =  $p . ' (' . $c . ')';
}

//ISBN
$datafield = $record->xpath('r:datafield[@tag="020"]');
if(count($datafield) > 0){
$datafield[0]->registerXPathNamespace('r', 'http://www.loc.gov/MARC21/slim');
$subfields = $datafield[0]->subfield;

foreach($subfields as $subfield){
$code = $subfield->attributes()->code;
switch($code){
case 'a':
$mediatheekItem->isbn = '' . $subfield;
break;
default:
break;
}
}
}

//TAAL
$datafield = $record->xpath('r:datafield[@tag="041"]');
if(count($datafield) > 0){
$datafield[0]->registerXPathNamespace('r', 'http://www.loc.gov/MARC21/slim');
$subfields = $datafield[0]->subfield;

foreach($subfields as $subfield){
$code = $subfield->attributes()->code;
switch($code){
case 'a':
$mediatheekItem->taal = '' . $subfield;
break;
default:
break;
}
}
}

//BLADZIJDEN
$datafield = $record->xpath('r:datafield[@tag="300"]');
if(count($datafield) > 0){
$datafield[0]->registerXPathNamespace('r', 'http://www.loc.gov/MARC21/slim');
$subfields = $datafield[0]->subfield;

foreach($subfields as $subfield){
$code = $subfield->attributes()->code;
switch($code){
case 'a':
$mediatheekItem->bladzijden = '' . $subfield;
break;
default:
break;
}
}
}

//UITGEVER
$datafield = $record->xpath('r:datafield[@tag="260"]');
if(count($datafield) > 0){
$datafield[0]->registerXPathNamespace('r', 'http://www.loc.gov/MARC21/slim');
$subfields = $datafield[0]->subfield;

$a = '';
$b;
$c;

foreach($subfields as $subfield){
$code = $subfield->attributes()->code;
switch($code){
case 'a':
$a = $subfield . ': ';
break;
case 'b':
$b = $subfield;
break;
case 'c':
$c = $subfield;
break;
default:
break;
}
}
$mediatheekItem->uitgever =  $a . $b;
$mediatheekItem->jaar =  '' . $c;
}

//AUTEURS
$datafield = $record->xpath('r:datafield[@tag="100"]');
if(count($datafield) > 0){
foreach($datafield as $df){
$df->registerXPathNamespace('r', 'http://www.loc.gov/MARC21/slim');
$subfields = $df->subfield;

foreach($subfields as $subfield){
$code = $subfield->attributes()->code;
switch($code){
case 'a':
$mediatheekItem->addAuteur('' . $subfield);
break;
default:
break;
}
}
}
}

//RUBRIEK EN TREFWOORDEN
$datafield = $record->xpath('r:datafield[@tag="650"]');
if(count($datafield) > 0){
$trefwoorden = array();

foreach($datafield as $df){
$df->registerXPathNamespace('r', 'http://www.loc.gov/MARC21/slim');
$subfields = $df->subfield;

$isRubriek = false;
$fieldValue;

foreach($subfields as $subfield){
$code = $subfield->attributes()->code;
switch($code){
case 'a':
$fieldValue = $subfield;
break;
case '9':
$isRubriek = true;
break;
default:
break;
}
}

if($isRubriek == true) {
$mediatheekItem->rubriek = '' . $fieldValue;
}
else{
    $trefwoorden[] = $fieldValue;
}
}
if(count($trefwoorden) > 0){
foreach($trefwoorden as $trefwoord){
$mediatheekItem->addTrefwoord('' . $trefwoord);
}
}
}

//PLAATSKENMERK
$datafield = $record->xpath('r:datafield[@tag="852"]');
if(count($datafield) > 0){
$datafield[0]->registerXPathNamespace('r', 'http://www.loc.gov/MARC21/slim');
$subfields = $datafield[0]->subfield;

foreach($subfields as $subfield){
$code = $subfield->attributes()->code;
switch($code){
case 'c':
$mediatheekItem->plaatskenmerk = '' . $subfield;
break;
default:
break;
}
}
}
$json .= json_encode($mediatheekItem);
}
// send response header for JSON content type
$app->response()->header('Content-Type', 'application/json');

$json .= ']';
$json .= '}';
//RETURN JSON STRING
echo sprintf("%s(%s)", helperJSONCallback($app), $json);
} catch(Exception $ex) {
//RETURN JSON STRING
echo sprintf("%s(%s)", helperJSONCallback($app), helperJSONMessage($ex->getMessage(), 'ERROR'));
}
});

$app->post('/mediatheeknewitems', function() use ($app, $_urlMediatheekBidocws, $_mediatheekBib){
try {
//FULL URL
$fullurl = $_urlMediatheekBidocws . '?' . '%23C=' . $_mediatheekBib . '&%23O=0&%23D=31';
//DO SOME INTERNET TRAFFIC
$content = file_get_contents($fullurl);
//CONTENTS IS HTML SO MAKE A DOM OF THE CONTENT
$doc = new DOMDocument();
$doc->recover = true;
$doc->strictErrorChecking = false;
@$doc->loadHTML($content);
$container = $doc->getElementById('bws-resultaat');//GET CONTAINER
$tables = $container->getElementsByTagName('table');
//GET FIRST TABLE
$table = $tables->item(0);
//GET ALL TR AND LOOP
$trs = $table->getElementsByTagName('tr');
//CREATE JSON FROM HTML
$json = '{';
$json .= '"results":[';
foreach ($trs as $tr) {
if($tr->hasAttributes()){
//CREATE A NEW MEDIATHEEK ITEM
$mediatheekItem = new MediatheekItemTop();
$tds = $tr->getElementsByTagName('td');
//GET FIRST TD (INFO ABOUT ITEM)
$td1 = $tds->item(0);
//GET FIRST HYPERLINK OF TD1 (GET ID)
$a1 = $td1->getElementsByTagName('a')->item(0);
$a1href = $a1->getAttribute('href');
$a1href = substr($a1href, strrpos($a1href, '=')+1);
//SET ID
$mediatheekItem->id = $a1href;
//GET SECOND TD (TYPE OF ITEM)
$td2 = $tds->item(1);
//SET CONTENT
$mediatheekItem->content = $a1->nodeValue;
//SET TYPE
$mediatheekItem->type = $td2->nodeValue;
//JSON ENCODE PHP OBJECT
$json .= json_encode($mediatheekItem);

$json .= ","; // adds comma after items for valid JSON
}
}
$json = substr($json, 0, -1); // removes comma after last result for valid JSON
$json .= ']';
$json .= '}';

// send response header for JSON content type
$app->response()->header('Content-Type', 'application/json');

//RETURN JSON STRING
echo sprintf("%s(%s)", helperJSONCallback($app), $json);
} catch(Exception $ex) {
//RETURN JSON STRING
echo sprintf("%s(%s)", helperJSONCallback($app), helperJSONMessage($ex->getMessage(), 'ERROR'));
}
});

$app->post('/mediatheekitem/:id', function($id) use ($app, $_urlMediatheekBidocws, $_mediatheekBib){
try {
//FULL URL?%23C=MAR&%23O=0&%23W=446235
$fullurl = $_urlMediatheekBidocws . '?' . '%23C=' . $_mediatheekBib . '&%23O=0&%23W=' . $id;
//DO SOME INTERNET TRAFFIC
$content = file_get_contents($fullurl);
//CONTENTS IS HTML SO MAKE A DOM OF THE CONTENT
$doc = new DOMDocument();
$doc->recover = true;
$doc->strictErrorChecking = false;
@$doc->loadHTML($content);
$tables = $doc->getElementsByTagName('table');
//GET FIRST TABLE
$table = $tables->item(0);
//GET ALL TR AND LOOP
$trs = $table->getElementsByTagName('tr');
//CREATE JSON FROM HTML
$json = '{';
$json .= '"results":[';
//GET LAST TR
$trlast = $trs->item($trs->length-1);
//GET ALL TDS
$tds = $trlast->getElementsByTagName('td');
//CREATE A NEW MEDIATHEEK ITEM Status
$mediatheekItem = new MediatheekItemStatus();
//SET ID
$mediatheekItem->id = $id;
//SET STATUS DESCRIPTION
$mediatheekItem->statusdescription = $tds->item(1)->nodeValue;
//SET STATUS
$mediatheekItem->status = ($tds->item(1)->getAttribute('class') == 'RED')?'0':'1';
//JSON ENCODE PHP OBJECT
$json .= json_encode($mediatheekItem);
$json .= ']';
$json .= '}';

// send response header for JSON content type
$app->response()->header('Content-Type', 'application/json');

//RETURN JSON STRING
echo sprintf("%s(%s)", helperJSONCallback($app), $json);
} catch(Exception $ex) {
//RETURN JSON STRING
echo sprintf("%s(%s)", helperJSONCallback($app), helperJSONMessage($ex->getMessage(), 'ERROR'));
}
});

// Function: Helper PDO
function getMediatheekDatabaseConnection() {
    $dbhost="127.0.0.1";
    $dbuser="root";
    $dbpass="";
    $dbname="mediatheek";
    $db = new PDO("mysql:host=$dbhost;dbname=$dbname", $dbuser, $dbpass);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    return $db;
}
function getUserDatabaseConnection() {
    $dbhost="127.0.0.1";
    $dbuser="root";
    $dbpass="";
    $dbname="users";
    $db = new PDO("mysql:host=$dbhost;dbname=$dbname", $dbuser, $dbpass);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    return $db;
}

// Function: Helper for jsonCallback
function helperJSONCallback($app){
    //GET CALLBACK
    $callback = $app->request()->get('callback');
    //SET RESPONSE HEADER
    if($callback == '?' || $callback == '')
        $app->response()->header("Content-Type", "application/json");
    else
        $app->response()->header("Content-Type", "application/javascript");
    return $callback;
}

// Function: Helper JSON Error Message
function helperJSONMessage($serverMessage, $message){
    $json = '{'
        . '"error":{'
        . '"servermessage":"'
        . $serverMessage
        . '",'
        . '"message":"'
        . $message
        . '"'
        . '}'
        . '}';
    return $json;
}

// Classes
class MediatheekItemStatus {
    //variables
    public $id, $status, $statusdescription;

    //private constructor function
    public function __construct(){
    }

    //string representation of object
    public function __toString(){
        return $id;
    }
}

class MediatheekItemTop {
    //variables
    public $id, $content, $type;

    //private constructor function
    public function __construct(){
    }

    //string representation of object
    public function __toString(){
        return $id;
    }
}

class MediatheekItem {
    //variables
    public $id, $titel, $type, $uitgever, $isbn, $bladzijden, $auteurs, $taal, $rubriek, $trefwoorden, $aanwinstnummer, $plaats, $plaatskenmerk;

    //private constructor function
    public function __construct(){
        $this->auteurs = array();
        $this->trefwoorden = array();
    }

    //add auteur
    public function addAuteur($auteurnaam){
        $auteur = new Auteur();
        $auteur->naam = $auteurnaam;
        array_push($this->auteurs, $auteur);
    }

    //add trefwoord
    public function addTrefwoord($refwoordnaam){
        $trefwoord = new Trefwoord();
        $trefwoord->naam = $refwoordnaam;
        array_push($this->trefwoorden, $trefwoord);
    }

    //string representation of object
    public function __toString(){
        return $title;
    }
}

class MediatheekItem2 {
    //variables
    public $id, $titel, $type, $uitgever, $isbn, $bladzijden, $cover, $auteurs, $taal, $rubriek, $trefwoorden, $aanwinstnummer, $plaats, $plaatskenmerk;

    //private constructor function
    public function __construct(){
        $this->auteurs = array();
        $this->trefwoorden = array();
    }

    //add auteur
    public function addAuteur($auteurnaam){
        $auteur = new Auteur();
        $auteur->naam = $auteurnaam;
        array_push($this->auteurs, $auteur);
    }

    //add trefwoord
    public function addTrefwoord($refwoordnaam){
        $trefwoord = new Trefwoord();
        $trefwoord->naam = $refwoordnaam;
        array_push($this->trefwoorden, $trefwoord);
    }

    //string representation of object
    public function __toString(){
        return $title;
    }
}

class Auteur {
    //variables
    public $naam;

    //private constructor function
    public function __construct(){}

    //string representation of object
    public function __toString(){
        return $naam;
    }
}
class Trefwoord {
    //variables
    public $naam;

    //private constructor function
    public function __construct(){}

    //string representation of object
    public function __toString(){
        return $naam;
    }
}