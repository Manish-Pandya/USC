<?php
require_once '../top_view.php';
?>

<div class="navbar">
<ul class="nav pageMenu" style="min-height: 50px; background: #e67e1d; color:white !important; padding: 2px 0 2px 0; width:100%">
	<li class="span3">
		<img src="../../img/hazard-icon.png" class="pull-left" style="height:50px" />
		<h2  style="padding: 11px 0 5px 85px;">Hazard Hub</h2>	
	</li>
	<li style="margin-top:2px;">
		<a class="addUser" style="text-shadow: none; color:white; background:#555" data-toggle="modal"  href="#addUser" ><img src='../../img/add-hazard-icon.png' style="margin-right:13px;">Add Hazard</a>
	</li>
</ul>
</div>
<div id="hazardTree" style="width:96%; padding:2%; clear:both;">
	<ul>
		<li rel="biological"><a href="#modal">Biological Hazards</a>
		
			<ul>
			<li rel="checklist" class="draggable"><a href="ChecklistHub.php">BLOODBORNE PATHOGENS (e.g. research involving human blood, body fluids, unfixed tissue) OSHA Bloodborne Pathogens (29 CFR 1910.1030)</a>
				<li rel="biological"><a href="#modal">Biosafety Level 1 (BSL-1)</a></li>
				<li rel="biological"><a href="#modal" id target="_blank">Biosafety Level 2 (BSL-2)</a></li>
				<li rel="biological"><a href="#modal">Biosafety Level 2+ (BSL-2+)</a></li>
				<li rel="biological"><a href="#modal">Biosafety Level 3 (BSL-3)</a>					
					<ul>
						<li rel="checklist" class="draggable"><a href="ChecklistHub.php">BLOODBORNE PATHOGENS (e.g. research involving human blood, body fluids, unfixed tissue) OSHA Bloodborne Pathogens (29 CFR 1910.1030)</a>
						<!-- 
						<li rel="biological" class="draggable"><a id="modalClick" href="#modal">Retroviricus zombicus</a>
							<ul>
								<li rel="checklist" class="draggable"><a href="ChecklistHub.php">Biosafety Level 3 (BSL-3)</a>
							</ul>
						</li>
						<li  class="draggable asdf fdsa"><a id="modalClick" href="#modal" target="_blank">"Baseball=Boring" says Matt </a></li>
						<li  class="draggable asdf fdsa"><a id="modalClick" href="#modal" target="_blank">La La Loopsy</a></li>
						<li  class="draggable asdf fdsa"><a id="modalClick" href="#modal" target="_blank"><img width="300" src="../../img/2011-12-10_14-14-20_770.jpg"/></a></li>
						 -->
					</ul>
				</li>
			</ul>
		</li>
		<li rel="chemical"><a href="#modal">Chemical Hazards</a></li>
		<li rel="radiation"><a href="#modal">Radiation Hazards</a></li>
		<li rel="equipment"><a href="#modal">Equipment Related Hazards</a></li>
	</ul>
</div>
<div id="modal" class="modal hide fade">
	<div class="modal-header">
		<p>test</p>
	</div>
	<div class='modal-body'>
		<p>test</p>
	</div>
	<div class='modal-footer'>
		<p>test</p>
	</div>
</div>
<script type="text/javascript">
$("#hazardTree").bind("loaded.jstree", function (event, data) {

})

//TODO:
//bind nodes to bootstrap modal method
.bind('select_node.jstree', function(e,data) { 
	href = data.rslt.obj.find('a').attr("href");
	if(href.indexOf('#') !=-1){
		//$(href).modal();
		$('#hazardTree').jstree("rename", data.rslt.obj);
		console.log(data.rslt.obj); 
	}else{
		//document.location.href=href;
		window.open(href, '_blank');
	}
})

.jstree({
	
	"crrm" : { 
		"move" : {
			"check_move" : function (m) { 
				//find the html/css classes of the dragged element
				array = m.o[0] + "";
				className =  m.o[0].className;
				if(className.indexOf('draggable') !=-1){
					//element has the class name 'draggable', so we return true, allowing it to be dragged.
					return true;
				}				
				return false;
			}
		}
	},
    "types" : {
        "types" : {
            "biological" : {
                "icon" : {
                    "image" : "../../img/biohazard-small-icon.png"
                }
            }, 

            "chemical" : {
                "icon" : {
                    "image" : "../../img/chemical-small-icon.png"
                }
            },
            
            "radiation" : {
                "icon" : {
                    "image" : "../../img/radiation-small-icon.png"
                }
            },
            
            "equipment" : {
                "icon" : {
                    "image" : "../../img/gear-small-icon.png"
                }
            },
            
            "checklist" : {
                "icon" : {
                    "image" : "../../img/checklist-small-icon.png"
                }
            }
        }
    },
    
    "plugins" : [ "html_data", "types", "themes", "dnd", "crrm", "ui", "contextmenu" ]
});
/*
$(function () {

	$("#demo")
		.bind("before.jstree", function (e, data) {
			$("#alog").append(data.func + "<br />");
		})
		.jstree({ 
			// List of active plugins
			"plugins" : [ 
				"themes","json_data","ui","crrm","cookies","dnd","search","types","hotkeys","contextmenu" 
			],

			// I usually configure the plugin that handles the data first
			// This example uses JSON as it is most common
			"json_data" : { 
				// This tree is ajax enabled - as this is most common, and maybe a bit more complex
				// All the options are almost the same as jQuery's AJAX (read the docs)
				"ajax" : {
					// the URL to fetch the data
					"url" : "/static/v.1.0pre/_demo/server.php",
					// the `data` function is executed in the instance's scope
					// the parameter is the node being loaded 
					// (may be -1, 0, or undefined when loading the root nodes)
					"data" : function (n) { 
						// the result is fed to the AJAX request `data` option
						return { 
							"operation" : "get_children", 
							"id" : n.attr ? n.attr("id").replace("node_","") : 1 
						}; 
					}
				}
			},
			// Configuring the search plugin
			"search" : {
				// As this has been a common question - async search
				// Same as above - the `ajax` config option is actually jQuery's AJAX object
				"ajax" : {
					"url" : "/static/v.1.0pre/_demo/server.php",
					// You get the search string as a parameter
					"data" : function (str) {
						return { 
							"operation" : "search", 
							"search_str" : str 
						}; 
					}
				}
			},
			// Using types - most of the time this is an overkill
			// read the docs carefully to decide whether you need types
			"types" : {
				// I set both options to -2, as I do not need depth and children count checking
				// Those two checks may slow jstree a lot, so use only when needed
				"max_depth" : -2,
				"max_children" : -2,
				// I want only `drive` nodes to be root nodes 
				// This will prevent moving or creating any other type as a root node
				"valid_children" : [ "drive" ],
				"types" : {
					// The default type
					"default" : {
						// I want this type to have no children (so only leaf nodes)
						// In my case - those are files
						"valid_children" : "none",
						// If we specify an icon for the default type it WILL OVERRIDE the theme icons
						"icon" : {
							"image" : "/static/v.1.0pre/_demo/file.png"
						}
					},
					// The `folder` type
					"folder" : {
						// can have files and other folders inside of it, but NOT `drive` nodes
						"valid_children" : [ "default", "folder" ],
						"icon" : {
							"image" : "/static/v.1.0pre/_demo/folder.png"
						}
					},
					// The `drive` nodes 
					"drive" : {
						// can have files and folders inside, but NOT other `drive` nodes
						"valid_children" : [ "default", "folder" ],
						"icon" : {
							"image" : "/static/v.1.0pre/_demo/root.png"
						},
						// those prevent the functions with the same name to be used on `drive` nodes
						// internally the `before` event is used
						"start_drag" : false,
						"move_node" : false,
						"delete_node" : false,
						"remove" : false
					}
				}
			},
			// UI & core - the nodes to initially select and open will be overwritten by the cookie plugin

			// the UI plugin - it handles selecting/deselecting/hovering nodes
			"ui" : {
				// this makes the node with ID node_4 selected onload
				"initially_select" : [ "node_4" ]
			},
			// the core plugin - not many options here
			"core" : { 
				// just open those two nodes up
				// as this is an AJAX enabled tree, both will be downloaded from the server
				"initially_open" : [ "node_2" , "node_3" ] 
			}
		})
		.bind("create.jstree", function (e, data) {
			$.post(
				"/static/v.1.0pre/_demo/server.php", 
				{ 
					"operation" : "create_node", 
					"id" : data.rslt.parent.attr("id").replace("node_",""), 
					"position" : data.rslt.position,
					"title" : data.rslt.name,
					"type" : data.rslt.obj.attr("rel")
				}, 
				function (r) {
					if(r.status) {
						$(data.rslt.obj).attr("id", "node_" + r.id);
					}
					else {
						$.jstree.rollback(data.rlbk);
					}
				}
			);
		})
		.bind("remove.jstree", function (e, data) {
			data.rslt.obj.each(function () {
				$.ajax({
					async : false,
					type: 'POST',
					url: "/static/v.1.0pre/_demo/server.php",
					data : { 
						"operation" : "remove_node", 
						"id" : this.id.replace("node_","")
					}, 
					success : function (r) {
						if(!r.status) {
							data.inst.refresh();
						}
					}
				});
			});
		})
		.bind("rename.jstree", function (e, data) {
			$.post(
				"/static/v.1.0pre/_demo/server.php", 
				{ 
					"operation" : "rename_node", 
					"id" : data.rslt.obj.attr("id").replace("node_",""),
					"title" : data.rslt.new_name
				}, 
				function (r) {
					if(!r.status) {
						$.jstree.rollback(data.rlbk);
					}
				}
			);
		})
		.bind("move_node.jstree", function (e, data) {
			data.rslt.o.each(function (i) {
				$.ajax({
					async : false,
					type: 'POST',
					url: "/static/v.1.0pre/_demo/server.php",
					data : { 
						"operation" : "move_node", 
						"id" : $(this).attr("id").replace("node_",""), 
						"ref" : data.rslt.cr === -1 ? 1 : data.rslt.np.attr("id").replace("node_",""), 
						"position" : data.rslt.cp + i,
						"title" : data.rslt.name,
						"copy" : data.rslt.cy ? 1 : 0
					},
					success : function (r) {
						if(!r.status) {
							$.jstree.rollback(data.rlbk);
						}
						else {
							$(data.rslt.oc).attr("id", "node_" + r.id);
							if(data.rslt.cy && $(data.rslt.oc).children("UL").length) {
								data.inst.refresh(data.inst._get_parent(data.rslt.oc));
							}
						}
						$("#analyze").click();
					}
				});
			});
		});

	});
	*/
</script>
<?php 
require_once '../bottom_view.php';
?>