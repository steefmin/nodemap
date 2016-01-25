<?php
include 'env.php';
$jsonfile = file_get_contents('results-links.json');
$oldjson = json_decode($jsonfile,true);
if($oldjson == []){
        exit(4);
}
$ip = $_GET['ip'];
$status ="good";
$number=1;
$nametemp = explode(":",$ip);
$nodes[0]=array("addr"=>$ip,"name"=>$nametemp[7]);
while($ip != $myip6){
        $text = '"'.$ip.'"';
        $text = "'NodeStore_nodeForAddr(".$text.")'";
        $output = shell_exec("nodejs ".$cjdnspath."/tools/cexec ".$text);
        $output = json_decode($output,true);
        if($output['result']==[]){
                print_r("ip lookup failed");
                $status = "unsuccesfull";
                break;
        }
        $addr=$output['result']['bestParent']['ip'];
        $nametemp = explode(":",$addr);
        $nodes[$number]=array("addr"=>$addr,"name"=>$nametemp[7]);
        if($addr>$ip){
                $edges[$number-1]=array("addr"=>$addr,"parent"=>$ip);
        }else{
                $edges[$number-1]=array("addr"=>$ip,"parent"=>$addr);
        }
        $ip = $output['result']['bestParent']['ip'];
        $number = $number+1;
}
if($status == "good"){
        $edges=array_values(array_merge($oldjson['edges'],$edges));
        $nodes=array_values(array_merge($oldjson['nodes'],$nodes));
        $edges=array_values(array_map("unserialize",array_unique(array_map("seri                                                                                                                               alize",$edges))));
        $nodes=array_values(array_map("unserialize",array_unique(array_map("seri                                                                                                                               alize",$nodes))));
        $json=array("nodes"=>$nodes,"edges"=>$edges);
        $fp = fopen('results-links.json', 'w');
        fwrite($fp, json_encode($json));
        fclose($fp);
}
?>

