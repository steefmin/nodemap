<?php
$path = "/path/to/cjdns/tools/pathfinderTree";
$pathfindtreefile = shell_exec('nodejs '.$path);
$jsonfile = file_get_contents('results.json');
$oldjson = json_decode($jsonfile,true);
$rows = explode("\n", $pathfindtreefile);
array_pop($rows);
foreach($rows as $nodenumber => $data){
        $node_data[$nodenumber] = explode(' ', $data); //split at each space
        $hops=strpos($data,"fc")/2;
        $addr=$node_data[$nodenumber][$hops*2];
        $nametemp=explode(":",$addr);
        $name=$nametemp[7];
        $label=$node_data[$nodenumber][$hops*2+1];

        if($hops!=0){//search for parent
                $i=$nodenumber-1;
                $found=0;
                while($found==0){
                        if($temp[$i]['hops']==$hops-1){
                                $parent=$temp[$i]["addr"];
                                $found=1;
                        }
                        $i=$i-1;
                }
        }else{
                $parent=0;
        }
        if($addr>$parent){
                $edges[$nodenumber]=array("addr"=>$addr,"parent"=>$parent);
        }else{
                $edges[$nodenumber]=array("addr"=>$parent,"parent"=>$addr);
        }
        $temp[$nodenumber]=array("addr"=>$addr,"name"=>$name,"label"=>$label,"hops"=>$hops);
        $nodes[$nodenumber]=array("addr"=>$addr,"name"=>$name);
}
$edges=array_values(array_merge($oldjson['edges'],$edges));
$nodes=array_values(array_merge($oldjson['nodes'],$nodes));
$edges=array_values(array_map("unserialize",array_unique(array_map("serialize",$edges))));
$nodes=array_values(array_map("unserialize",array_unique(array_map("serialize",$nodes))));
$json=array("nodes"=>$nodes,"edges"=>$edges);
$fp = fopen('results.json', 'w');
fwrite($fp, json_encode($json));
fclose($fp);
?>
