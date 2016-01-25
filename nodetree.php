<html>
<?php
$ip = $_SERVER['REMOTE_ADDR'];
include 'linksjson.php?ip='.$ip //generate new json file
?>
<head>
<link rel="shortcut icon" href="/favicon.ico"></link>
<script src="../jquery-2.2.0.min.js"></script>
<script type="text/javascript" src="../VivaGraphJS/dist/vivagraph.js"></script>
<script type="text/javascript">
function main () {
        var graph = Viva.Graph.graph();

        getjson(graph); //add the nodes and links

        var graphics = Viva.Graph.View.svgGraphics();

        var highlightNodes = function(nodeId, isOn){
                graph.forEachLinkedNode(nodeId, function(node, link){
                        var linkUI = graphics.getLinkUI(link.id);
                        if(linkUI){
                                linkUI.attr('stroke', isOn ? 'red':'gray');
                        }
                });
        };

        graphics.node(function(node) {
                //console.log(node.data);
                var ui = Viva.Graph.svg('g');
                var text = Viva.Graph.svg('text').text(node.data.name);
                var circ = Viva.Graph.svg('circle').attr("r",4).attr("fill",node.data.color).attr("id",node.id);
                ui.append(text);
                ui.append(circ);
                $(ui).hover(function(){
//                      $(this).append(text);
                },function(){
//                      $(this).find(text).remove();
                });
                $(ui).click(function(){
                        var prenode = $('circle[fill="#000000"]');
                        if(prenode.get(0) !== undefined){
                                prenode.attr("fill","#0000ff");
//                              console.log(prenode.get(0).id);
                                highlightNodes(prenode.get(0).id,false);
                        }
                        var thenode = document.getElementById(node.id);
                        thenode.attr("fill","#000000");
                        highlightNodes(node.id,true);
                        $("#infoform").html(nodeinfoform(graph,node.id));

                });
                return ui;
        }).placeNode(function(nodeUI,pos) {
                nodeUI.attr('transform','translate('+(pos.x-0)+','+(pos.y-0)+')');
        });

        graphics.link(function(link){
                var stroke = Viva.Graph.svg('path').attr('stroke', 'gray');
                return stroke;
        }).placeLink(function(linkUI, fromPos, toPos){
                var data = 'M'+fromPos.x+','+fromPos.y+'L'+toPos.x+','+toPos.y;
                linkUI.attr("d",data);
        });

        var layout = Viva.Graph.Layout.forceDirected(graph, {
                springLength : 100,     //180
                springCoeff : 0.0002,   //0.0002
                dragCoeff : 0.03,       //0.03
                gravity : -3.2          //-1.2
        });
        var renderer = Viva.Graph.View.renderer(graph,{graphics:graphics,layout:layout});
        renderer.run();

        $("#title").click(function(){
                $("#infoform").html("");
        });
}
function getjson(graph) {
        $.getJSON('results-links.json', function(json) {
                console.log("nodes: "+json.nodes.length);
                graph.addNode(json.nodes[0].addr,{name:json.nodes[0].name, color:'#ff0000', defaultcolor:'#ff0000'});
                for(var i=1; i < json.nodes.length; i++){
                        graph.addNode(json.nodes[i].addr,{name:json.nodes[i].name, color:'#0000ff', defaultcolor:'#0000ff'});
                }
                console.log("edges: "+json.edges.length);
                for(var i=0; i < json.edges.length; i++){
                        graph.addLink(json.edges[i].addr,json.edges[i].parent);
                }
                $("#jsonstats").html("nodes: "+json.nodes.length+"<br>edges: "+json.edges.length+"<br><a href='http://www.github.com/steefmin/nodemap'>source</a>");
        });
}
function nodeinfoform(graph,nodeid){
        var linkednodes = connectednodes(graph,nodeid);
        var htmltext =  ""+
                        "<div style='font-family: monospace;'>"+
                        "<a href='http://["+nodeid+"]' target='_blank'>"+nodeid+"</a>"+
                        "</div>"+
                        "<br>"+
                        "<a href='http://fc00.org#"+nodeid+"'>fc00.org</a>"+
                        "<br>"+
                        linkednodes+
                        "";
        return htmltext;
}
function connectednodes(graph,nodeid){
        var linkednodes="";
        var numberofnodes=0;
        graph.forEachLinkedNode(nodeid, function(linkednode,link){
                linkednodes = linkednodes+linkednode.id+"<br>";
                numberofnodes++
        });
        var totaltext = "Peers: "+
                        numberofnodes+
                        "<br><div style='font-family:monospace'>"+
                        linkednodes+
                        "</div>";
        return totaltext;
}
</script>
<style type="text/css" media="screen">
html, body, svg {
        width: 99%;
        height: 99%;
        font-family: monospace;
        font-size:10pt;
}
a {
        color: #1111ff;
        text-decoration: none;
}
a:visited {
        color: #5555ff;
}
div#title{
        float:left;
        top:5px;
        left:5px;
        font-size:24pt;
        font-family: monospace;
        background-color: transparent;
}
div.screeninfo {
        background-color: #dddddd;
        border-radius: 3px;
        padding: 4px;
        position: absolute;
        font-family: Arial;
        color: #000000;
}
div#infoform {
        float:right;
        top:5px;
        right:5px;
        width:312px;
        text-align: right;
}
div#jsonstats{
        float:left;
        bottom:5px;
        left:5px;
}
</style>
</head>
<body onload='main()'>
<div id="title" class="screeninfo">
/root/nodemap/
</div>
<div id="infoform" class="screeninfo">
        <div id="original" style="text-align:justify;">
<p>
All the nodes visible from my node, 90d6 in red.
Nodes are added every fifteen minutes.
Click nodes for more information about it.
Give the graph a minute to stabilize. I'm crunching numbers on the physics to improve this.
</p><p>
This can be considered as version 2 of nodemap. The first itteration used the pathfindertree tool,
but the validity of a lot of links were questionable. So I looked further for a more reliable
method to find paths. Now a recursive script is used that step by step analyses that path to nodes.
It will fail a bit more often, but I'm more sure of the validity of this method. This map will
need some time to grow as large as the previous version. I've given it a list of nodes to parse to
start off. Visits to this site are also included into the graph.
</p><p>
Next step is to time-code the links and scrub the list of old links that are not relevant anymore.
This should keep the map up-to-date on the actual situation.
</p>

        </div>
</div>

<div id="jsonstats" class="screeninfo">
</div>

</body>
</html>
