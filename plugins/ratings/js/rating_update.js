if (document.images){
  pic1 = new Image(220,19); 
  pic1.src = "img/rating_loading.gif"; 

  pic2 = new Image(25,75); 
  pic2.src = "img/rating_star.gif"; 

  pic3 = new Image(25,75); 
  pic3.src = "img/rating_star_2.gif"; 
  
  pic4 = new Image(16,13); 
  pic4.src = "img/rating_tick.gif";
  
  pic5 = new Image(14,14); 
  pic5.src = "img/rating_warning.gif";
}

var xmlHttp

function GetXmlHttpObject(){

var xmlHttp = null;

	try {
	  xmlHttp = new XMLHttpRequest();
	  }
	catch (e) {
	  try {
			xmlHttp = new ActiveXObject("Msxml2.XMLHTTP");
		}
	  catch (e){
			xmlHttp = new ActiveXObject("Microsoft.XMLHTTP");
		}
	  }
	  
	return xmlHttp;

}

function rate(rating,id,show5,showPerc,showVotes){

	xmlHttp = GetXmlHttpObject()
	
	if(xmlHttp == null){
		alert ("Your browser does not support AJAX!");
		return;
	  }

	xmlHttp.onreadystatechange = function(){
		
	var loader = document.getElementById('loading_'+id);
	var uldiv = document.getElementById('ul_'+id);
	
		if (xmlHttp.readyState == 4){ 
			
			var res = xmlHttp.responseText;
			
			if(res == 'already_voted'){
				
				loader.style.display = 'block';
				loader.innerHTML = '<div class="voted_twice">You already voted!</div>';
				
			} else {
				
				loader.style.display = 'block';
				loader.innerHTML = '<div class="voted">Thanks for voting!</div>';

				if(show5 == true){
					var out = document.getElementById('outOfFive_'+id);
					var calculate = res/20;
					out.innerHTML = Math.round(calculate*100)/100;
				} 
				
				if(showPerc == true){
					var perc = document.getElementById('percentage_'+id);
					var newPerc = res;
					perc.innerHTML = newPerc+'%';
				}
				
				else if(showPerc == false){
					var newPerc = res;
				}
				
				if(showVotes == true){
					var votediv = document.getElementById('showvotes_'+id).firstChild.nodeValue;
					var splitted = votediv.split(' ');
					var newval = parseInt(splitted[0]) + 1;
					if(newval == 1){
						document.getElementById('showvotes_'+id).innerHTML = newval+' Vote';
					} else {
						document.getElementById('showvotes_'+id).innerHTML = newval+' Votes';
					}
				}
				
				var ulRater = document.getElementById('rater_'+id);
				ulRater.className = 'star-rating2';
				
				var all_li = ulRater.getElementsByTagName('li');
				
				for(var i=1;i<all_li.length;i++){
					
					all_li[i].getElementsByTagName('a')[0].onclick = 'return false;';
					all_li[i].getElementsByTagName('a')[0].setAttribute('href','#');
					
				}
				
				if(navigator.appName == 'Microsoft Internet Explorer'){
					uldiv.style.setAttribute('width',newPerc+'%'); // IE
				 } else {
					uldiv.setAttribute('style','width:'+newPerc+'%'); // Everyone else
				 }
				
			}
		} else {
			loader.innerHTML = '<img src="img/rating_loading.gif" alt="loading" />';	
		}
	
	}
	var url = "plugin.php?r=ratings";
	var params = "id="+id+"&rating="+rating;
	xmlHttp.open("POST",url,true);
	xmlHttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	xmlHttp.setRequestHeader("Content-length", params.length);
	xmlHttp.setRequestHeader("Connection", "close");
	xmlHttp.send(params);
	
	$.ajax({
		success: function(html){
			location.reload();
		}
	});

} 