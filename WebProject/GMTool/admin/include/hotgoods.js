function HotGoods()
{
	var w = 450;
	var h = 300;
	var r = 100;
	var wGraph = 1000;
	var tweenDuration = 250;

	//OBJECTS TO BE POPULATED WITH DATA LATER
	var newData = [];    
	var oldData = [];
	var filteredPieData = [];
	var result = [];

	//D3 helper function to create colors from an ordinal scale
	var color = d3.scale.category20();

	var server_group;
	var servers;
	var date_group;
	var dates;
	var graph;
	var rect;
	var numLabel;
	var  goodsName;
	
	
	this.draw = function(divId){
		var selection = '#' + divId;
		server_group = d3.select(selection).append("svg:svg")
		  .attr("width", w)
		  .attr("height", h);
		date_group = d3.select('#date').append("svg:svg")
		.attr("width",250)
		.attr("height", h);
		graph = d3.select('#graph').append("svg:svg")
		.attr("width",wGraph)
		.attr("height",h);
	};
	
	var resize = function(width, height, size){
		var maxInARow = Math.floor(width / 100); //假设一个服的宽度为4
		var rowNumber = Math.floor(size / maxInARow) + 1; //总共有几行
		var realNumberInALine = Math.ceil(size / rowNumber); //真实情况下每行有几个服
		var intervalX = width / (realNumberInALine + 1); //每两个之间的间隔
		var intervalY = height / (rowNumber + 1);
//		alert("width: "+width+"max in a row: "+maxInARow +","+intervalX+","+intervalY);
		var x = new Array(size);
		var y = new Array(rowNumber);
		var currentX = intervalX/2;
		var currentY = intervalY/2;
		for(var index = 0; index < size; index++){
			x[index] = currentX; 
			y[index] = currentY;
			currentX += intervalX;
			if(currentX > width - intervalX){
				currentX = intervalX/2;
				currentY += intervalY;
			}
		}
		return function(i){
			return {'x':x[i],'y':y[i]};
		}
	};

	
//	更新矩形	
	var updateRec = function(data){
		if(data.length > 0){
			var max = data[0].num;
			var min = data[data.length - 1].num;
			var heightScale = d3.scale.linear();
			heightScale.domain([min, max]).range([40, h-40]);
			var width = (wGraph-20) / data.length;
			var yScale = function(d){ return h-20-heightScale(d);}
			rect = graph.selectAll("rect").data(data);
			rect
			.transition()
			.attr("x", function(d,i){ return 10 + i*width;} )
			.attr("y",function(d){return yScale(d.num)})
			.attr("width", width)
			.attr("height",function(d){return heightScale(d.num)})
			
			rect.enter().append("rect")
			.attr("x", function(d,i){ return 10 + i*width;} )
			.attr("width", width)
			.attr("fill",function(d,i){return color(i)})
			.on("mouseover", function(d,i){
				d3.select(this).attr("fill","#DDDDDD");
				var label = graph.selectAll("text").data([1]);
				label.enter().append("svg:text")
				.text("["+d.goodsId+"] ["+d.num+"个]")
				.attr("dy", function(d1){return yScale(d.num)+20})
				.attr("dx", function(d1,i1){return 10 + (i + 0.5)*width;});
				
				label.text("["+d.goodsId+"] ["+d.num+"个]")
				.attr("dy", function(d1){return yScale(d.num)+20})
				.attr("dx", function(d1,i1){return Math.min(10 + (i + 0.5)*width,wGraph-250);});
			})
			.on("mouseout", function(d,i){
				    	  d3.select(this).transition()
				    	  .attr("fill", color(i));
				    	  graph.selectAll("text").text("");
				      })
			.transition()
			.attrTween("height", function(d,i){
				  var i = d3.interpolate(0, heightScale(d.num));
				  return function(t) {
					  return i(t);
				  }
			})
			.attrTween("y", function(d,i){
				var i = d3.interpolate(h-20, yScale(d.num));
				return function(t){
					return i(t);
				}
			});
			rect.exit()
			.transition()
			.attr("y",h-20)
			.attr("height",0)
			.remove();
				
		}else{
			clearRecData();
		}
		
	}
	//清楚矩形数据
	var clearRecData = function(){
		var data = [];
		rect = graph.selectAll("rect").data(data);
		rect.exit()
		.transition()
		.attr("y",h-20)
		.attr("height",0)
		.remove();
		numLabel = graph.selectAll("text").data(data);
		numLabel.exit().remove();
		rect.exit()
		.transition()
		.attr("y",h-20)
		.attr("height",0)
		.remove();
	}
	
	var clearDateData = function(){
		var data = [];
		dates = date_group.selectAll("text").data(data);
		dates.exit().transition()
		.attr("fill","white")
		.remove();
	}
	
	var clearServer = function(){
		result = [];
		  servers = server_group.selectAll("text").data(result);
		  servers.exit().transition()
		  .attr("fill","white")
	      .remove();
	}
	
	var onclick = function(d){
		clearDateData();
		clearRecData();
		var interval = Math.max(30, 300/d.length);
		dates = date_group.selectAll("text").data(d);
		dates.enter().append("svg:text")
			.attr("fill","black")
			.attr("dx", function(d,i){return 50 + 70 * Math.floor(i / 10);})
			.attr("dy", function(d, i){return 20+ (i % 10) * interval; })
			.text(function(d){ return d.day})
			.on("mouseover", function(d, i){
				updateRec(d.goods);
			})
		dates.exit().remove();
	};
	
	this.update = function(result){
		  if(result.length > 0){
			  var newScale = resize(w, h, result.length);
			  //alert(newScale(0) + "," + newScale(1));
			  //DRAW ARC PATHS
			    servers = server_group.selectAll("text").data(result);
			    servers.text(function(d){return d.server});
			    servers.enter().append("svg:text")
			      .text(function(d){return d.server})
			      .on("mouseover", function(d,i){
			    	  d3.select(this).transition()
			    	  .attr("fill", color(i))
			    	  .style("font-size","25px");
			      })
			      .on("mouseout", function(d,i){
			    	  d3.select(this).transition()
			    	  .attr("fill", "grey")
			    	  .style("font-size","15px");
			      })
			      .on("click", function(d, i){
			    	  var jsonArr = new Array();
			    	  for(var o in d){
			    		  if(o != "server"){
			    			  var obj = {
			    					  goods: d[o],
			    					  day:o
			    			  };
			    			  jsonArr.push(obj);
			    		  }
			    	  }
			    	  onclick(jsonArr);
			      })
			      .transition()
			      .attrTween("dx", function(d,i){
			    	  	var b = d3.interpolate(225, newScale(i).x);
			    	  	return function(t){
			    	  		return b(t);
			    	  	}})
			      .attrTween("dy", function(d,i){
			    	  	var b = d3.interpolate(150, newScale(i).y);
			    	  	return function(t){
			    	  		return b(t);
			    	  	}})  	
			      .attr("fill", "grey")
			    servers.exit()
			      .remove();
		  }else{
			  servers = server_group.selectAll("text").data(result);
			  servers.exit().transition()
			  .attr("fill","white")
		      .remove();
		  }
	};
	
	this.clear = function(){
		clearServer();
		clearDateData();
		clearRecData();
	};
	
}
