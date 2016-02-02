<?php

$CJDNSDIR = "/home/cjdns/cjdns";

function dumpPeers($CJDNSDIR,$node){
        $string = "RouterModule_getPeers('".$node."',30000,undefined)";
        $output = shell_exec('nodejs '.$CJDNSDIR.'/tools/cexec "'.$string.'"');
//      $output = shell_exec('python '.$CJDNSDIR.'/contrib/python/cexec "'.$string.'"');
        if(preg_match("~peers~",$output)==1){ //if peers is found in output
                $output = json_decode($output,true);
                return $output['peers'];
        }else{
                return 0;
        }
}
function pktoip6($key){
        $output = shell_exec('python /home/cjdns/cjdns/contrib/python/pktoip6 '.$key);
        $output = explode(" ---> ",$output);
        $addr = $output[1];
        return $addr;
}

function storeNode($nodes,$key,$peerkeys){
        $peers = [];
        foreach($peerkeys as $peer){
                $temp = explode(".",$peer);
                $peers[] = $temp[5].".k";
        }
        array_shift($peers);
//      $addr = pktoip6($key);
//      $nametemp = explode(":",$addr);
//      $name = $nametemp[7];
//      $nodes[$key]=array("peers"=>$peers,"addr"=>$addr,"name"=>$name);
        $nodes[$key] = $peers;
        return $nodes;
}
function splice($CJDNSDIR,$gohere,$viahere){
        $output = shell_exec('nodejs '.$CJDNSDIR.'/tools/splice '.$gohere.' '.$viahere);
        $output = str_replace("\n", "" ,$output);
        return $output;
}
$teller = 0;
$nodes = [];
$mynode = "0000.0000.0000.0001";
$list = dumpPeers($CJDNSDIR,$mynode); //initialize list
print_r($list);
while(count($list)>0){
        // als node al via een andere path is bereikt, doe skip
        $keytemp=explode(".",$list[0]);
        $key=$keytemp[5].".k";
        $path=$keytemp[1].".".$keytemp[2].".".$keytemp[3].".".$keytemp[4];
        if(isset($nodes[$key])){
                array_shift($list);
                continue;
        }
        //output some info
        print_r("Now doing: ".$key."\n");
        print_r("Nodes scanned: ".count($nodes)." | Nodes todo: ".count($list)."\n");
        // dumppeers van node bovenaan $list
        $peerkeys = dumpPeers($CJDNSDIR,$list[0]);
        // sla op: node[key] = addr , name , peerlijst
        $nodes = storeNode($nodes, $key, $peerkeys);
        // kijk of peer al gedaan is, zo ja -> niet opslaan in list
        // sla nieuwe peers op onderaan $list
        if($peerkeys == 0){     //als geen peers gevonden zijn, skip vooruit
                array_shift($list);
                continue;
        }
        array_shift($peerkeys);
        foreach($peerkeys as $peer){
                $test = explode(".",$peer);
                $destination = $test[1].".".$test[2].".".$test[3].".".$test[4];
                if(!isset($nodes[$test[5]."k"])){
                        $newpath = splice($CJDNSDIR,$destination,$path);
                        $list[] = $test[0].".".$newpath.".".$test[5].".k";
                }
        }
        //remove duplicates
        $list = array_unique($list);
        // array_shift $list
        array_shift($list);
        //testcode
        if($teller > 10000){
                break;
        }
        $teller = $teller +1;
}
print_r($nodes);

$fp = fopen('/media/usb/www/html/cjdns/nodes.json', 'w');
fwrite($fp, json_encode($nodes));
fclose($fp);

?>
