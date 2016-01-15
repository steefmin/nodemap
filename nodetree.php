<html>
<?php
include 'pathjson.php' //generate new json file
?>
<head>
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
                var rect = Viva.Graph.svg('circle').attr("r",4).attr("fill",node.data.color).attr("id",node.id);
                ui.append(text);
                ui.append(rect);
                $(ui).hover(function(){
//                      $(this).append(text);
                        highlightNodes(node.id,true);
                },function(){
//                      $(this).find(text).remove();
                        highlightNodes(node.id,false);
                });
                $(ui).click(function(){
                        var prerect = $('rect[fill="#000000"]');
                        prerect.attr("fill","#0000ff");
                        var therect = document.getElementById(node.id);
                        therect.attr("fill","#000000");
                        $("#infoform").html(nodeinfoform(node.id));

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
                springLength : 100,
                springCoeff : 0.001,
                dragCoeff : 0.02,
                gravity : -5.2
        });
                var renderer = Viva.Graph.View.renderer(graph,{graphics:graphics,layout:layout});
                renderer.run();
}
function getjson(graph) {
        $.getJSON('results.json', function(json) {
                console.log("Nodes: "+json.nodes.length);
                graph.addNode(json.nodes[0].addr,{name:json.nodes[0].name, color:'#ff0000', defaultcolor: "#ff0000"});
                for(var i=1; i < json.nodes.length; i++){
                        graph.addNode(json.nodes[i].addr,{name:json.nodes[i].name, color:'#0000ff', defaultcolor: "#0000ff"});
                }
                console.log("Edges: "+json.edges.length);
                for(var i=1; i < json.edges.length; i++){
                        graph.addLink(json.edges[i].addr,json.edges[i].parent);
                }
                $("#jsonstats").html("Nodes: "+json.nodes.length+"<br>Edges: "+json.edges.length+"<br><a href='http://www.github.com/steefmin/nodemap'>source</a>");
        });
}
function nodeinfoform(nodeid){
        var htmltext =  ""+
                        "<pre>"+
                        "<a href='http://["+nodeid+"]' target='_blank'>"+nodeid+"</a>"+
                        "</pre>"+
                        "<br>"+
                        "<a href='http://fc00.org#"+nodeid+"'>fc00.org</a>"+
                        "";
        return htmltext;
}
</script>
<style type="text/css" media="screen">
html, body, svg {
        width: 99%;
        height: 99%;
        font-family: monospace;
        font-size:10pt;
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
        <div style="text-align:justify;">
All the nodes visible from my node, 90d6 in red.
Nodes are added every five minutes.
Click nodes to see the adress and a link to fc00.org for a more complete network view.
Hover over nodes to view short-name and see the connecting links.
Give the graph a minute to stabilize. I'm crunching numbers on the physics to improve this.
        </div>
</div>

<div id="jsonstats" class="screeninfo">
</div>

</body>
</html>
