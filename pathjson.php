<?php
$path = "/path/to/cjdns/tools/pathFinderTree";
$pathfindtreefile = shell_exec('nodejs '.$path);

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
$json=array("nodes"=>$nodes,"edges"=>$edges);
$fp = fopen('results-links.json', 'w');
fwrite($fp, json_encode($json));
fclose($fp);
?>
