<?php


$app->post('/debug/mediatheekitems', function() use ($app, $_urlMediatheekBidocsru, $_mediatheekBib, $_mediatheekSearchStartindex, $_mediatheekSearchEndindex, $_mediatheekSearchString, $_mediatheekSearchField){
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
            $id_field = '';

            if(!$firstitem)
                $json .= ',';
            else
                $firstitem = false;
//CREATE A NEW MEDIATHEEK ITEM
            $mediatheekItem = new MediatheekItem2();

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
                $id_field = '' . $controlfield[0];
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
                            //delete '-' from ISBN
                            $subfield = str_replace('-', '', $subfield);
                            $subfield = str_replace(' ', '', $subfield);
                            $mediatheekItem->isbn = '' . $subfield;
                            $cover = getCover(''.$subfield, $id_field);
                            $mediatheekItem->cover = $cover;
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


function getCover($bookisbn, $bookid){
    if($bookisbn != null){
        $_googlebooksIsbn = "https://www.googleapis.com/books/v1/volumes?q=isbn:";
        $booksurl = $_googlebooksIsbn . $bookisbn;
        $content = file_get_contents($booksurl);
        $content_json = json_decode($content,true);
        //return print_r($content_json);
        try{
            $image_url = $content_json["items"][0]['volumeInfo']['imageLinks']['thumbnail'];
            //insert cover in database
            insertCover($image_url, $bookid);
            // return the cover-url
            return $image_url;
        }
        catch(Exception $e){
            return "nocover";
        }
    }
    else{
        return 'nocover';
    }
}

// INSERT COVER IN DATABASE VIA REDBEAN
function insertCover($imageUrl, $bookId){
    $books = R::dispense('books');
    $books->bookid = (int)$bookId;
    $books->cover = $imageUrl;
    $book = R::store($books);

}