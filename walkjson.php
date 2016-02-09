<?php
$time = microtime(true);
$CJDNSDIR = "/home/cjdns/cjdns";

function dumpPeers($node){
        global $CJDNSDIR;
//	$string = "RouterModule_getPeers('".$node."',30000,undefined)";
//	$output = shell_exec('nodejs '.$CJDNSDIR.'/tools/cexec "'.$string.'"');
	$string = "RouterModule_getPeers('".$node."',0,0)";
	$output = shell_exec(''.$CJDNSDIR.'/contrib/python/cexec "'.$string.'"');
        if(preg_match("~peers~",$output)==1){ //if peers is found in output 
		$pattern = '~v..\..{4}\..{4}\..{4}\..{4}\..{52}\.k~';
		preg_match_all($pattern, $output, $result);
                return $result[0];
        }else{
                return 0;
        }
}
function pktoip6($key){
        global $CJDNSDIR;
        $output = shell_exec('python '.$CJDNSDIR.'/contrib/python/pktoip6 '.$key);
        $output = explode(" ---> ",$output);
        $addr = $output[1];
        return $addr;
}
function storeNode($nodes,$key,$peerkeys){
        $peers = [];
        if($peerkeys!=0){
                foreach($peerkeys as $peer){
                        $temp = explode(".",$peer);
                        $peers[] = $temp[5].".k";
                }
                array_shift($peers);
        }
	$addr = pktoip6($key);
	$nametemp = explode(":",$addr);
	$name = $nametemp[7];
	$nodes[$key]=array("peers"=>$peers,"addr"=>$addr,"name"=>$name);
//	$nodes[$key] = $peers;
        return $nodes;
}
function splice($gohere,$viahere){
        global $CJDNSDIR;
        $output = shell_exec('nodejs '.$CJDNSDIR.'/tools/splice '.$gohere.' '.$viahere);
        $output = str_replace("\n", "" ,$output);
        return $output;
}
function getnewpath($key){
	global $CJDNSDIR;
	$ip6 = pktoip6($key);
	$string = "NodeStore_nodeForAddr('".$ip6."')";
	$output = shell_exec(''.$CJDNSDIR.'/contrib/python/cexec "'.$string.'"');
	preg_match('~.{4}\..{4}\..{4}\..{4}~',$output,$path);
	return $path;
}
$nodes = [];
$mynode = "0000.0000.0000.0001";
$list = dumpPeers($mynode);//initialize list
while(count($list)>0){
//	break;
        // als node al via een andere path is bereikt, doe skip
        $keytemp=explode(".",$list[0]);
        $key=$keytemp[5].".k";
        $path=$keytemp[1].".".$keytemp[2].".".$keytemp[3].".".$keytemp[4];
        if(isset($nodes[$key])){
                array_shift($list);
                continue;
        }
        //output some info
        print_r("Now doing: ".$path."\n");
        print_r("Nodes scanned: ".count($nodes)." | Nodes todo: ".count($list)."\n");
        // dumppeers van node bovenaan $list
        $peerkeys = dumpPeers($list[0]);
        // sla op: node[key] = addr , name , peerlijst
        $nodes = storeNode($nodes, $key, $peerkeys);
        // kijk of peer al gedaan is, zo ja -> niet opslaan in list
        // sla nieuwe peers op onderaan $list
        if($peerkeys == 0){ //als geen peers gevonden zijn, skip vooruit
                array_shift($list);
                continue;
        }
        array_shift($peerkeys);
        foreach($peerkeys as $peer){
                $test = explode(".",$peer);
                $destination = $test[1].".".$test[2].".".$test[3].".".$test[4];
                if(!isset($nodes[$test[5].".k"])){
                        $newpath = splice($destination,$path);
			if($newpath == "ffff.ffff.ffff.ffff"){
				$path = getnewpath($key);
				$newpath = splice($destination,$path);
			}
			//print_r($newpath."\n");
                        $list[] = $test[0].".".$newpath.".".$test[5].".k";
                }
        }
        //remove duplicates
//        $list = array_unique($list);
        // array_shift $list
        array_shift($list);
}
//print_r($nodes);
print_r("Total nodes: ".count($nodes)."\n");
$fp = fopen('nodes.json', 'w');
fwrite($fp,json_encode($nodes));
fclose($fp);
$elapsed_time = (microtime(true)-$time)/60;
print_r("Time used: ".$elapsed_time." minutes\n");
?>
