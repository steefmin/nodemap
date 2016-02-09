<html>
<head>
<link rel="shortcut icon" href="/favicon.ico"></link>
<script src="../jquery-2.2.0.min.js"></script>
<script src="../VivaGraphJS/dist/vivagraph.min.js"></script>
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
//			$(this).append(text);
		},function(){
//			$(this).find(text).remove();
		});
		$(ui).click(function(){
			var prenode = $('circle[fill="#000000"]');
			if(prenode.get(0) !== undefined){
				prenode.attr("fill","#0000ff");
//				console.log(prenode.get(0).id);
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
		springLength : 180, 	//180
		springCoeff : 0.0002,	//0.0002
		dragCoeff : 0.03,	//0.03
		gravity : -1.2		//-1.2
	});

	var renderer = Viva.Graph.View.renderer(graph,{
		graphics:graphics,
		layout:layout
	});
	renderer.run();

	$("#title").click(function(){
		$("#infoform").html("");
	});


}
function getjson(graph) {
	$.getJSON('nodes.json', function(json) {
		var nodesteller = 0;
		var edgesteller = 0;
		for(var key in json){
			if(json.hasOwnProperty(key)){
				if(key == "<YOUR PUBLICKEY>"){
				graph.addNode(key.slice(0,52),{name: json[key].name,addr: json[key].addr ,color:'#ff0000', isPinned: true});
				}else{
				graph.addNode(key.slice(0,52),{name: json[key].name,addr: json[key].addr ,color:'#0000ff'});
				}
			}
		nodesteller++;
		}
		for(var key in json){
			for(var n=0; n<json[key].peers.length; n++){
				var alreadyLinked = 0;
				graph.forEachLinkedNode(json[key].peers[n].slice(0,52),function(linkedNode, link){
					if(linkedNode.id == key.slice(0,52)){
						alreadyLinked = 1;
					}
				});
				if(alreadyLinked==0){
					graph.addLink(key.slice(0,52),json[key].peers[n].slice(0,52));
					edgesteller++;
				}
			}
		}

		$("#jsonstats").html("nodes: "+nodesteller+"<br>edges: "+edgesteller+"<br><a href='http://www.github.com/steefmin/nodemap'>source</a>");
	});

}
function nodeinfoform(graph,nodeid){
	var linkednodes = connectednodes(graph,nodeid);
	var nodeinfo = graph.getNode(nodeid);
	var htmltext =  ""+
			"<div style='font-family: monospace;'>"+
			"<a href='http://["+nodeinfo.data.addr+"]' target='_blank'>"+nodeinfo.data.addr+"</a>"+
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
		var nodeinfo = graph.getNode(linkednode.id);
		linkednodes = linkednodes+nodeinfo.data.addr+"<br>";
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
</p><p>
Version 3 already.
With a new crawler that this time itteratively asks peers for their peers.
No more lists of adresses to search for, or scanning randomly for existing nodes.
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
