var xhttp = new XMLHttpRequest();
xhttp.onreadystatechange = function() {
	if(this.readyState == 4 && this.status == 200) {
		var data = JSON.parse(this.response);
		for(var i=0; i<data.length; i++) {
			var div = document.createElement("div");
			var img = document.createElement("img");
			img.src = data[i];
			div.setAttribute("class", "image");
			div.appendChild(img);
			document.body.appendChild(div);
			div.addEventListener("click", function() {
				this.classList.toggle("image-selected");
			});
			div.addEventListener("mouseover", function(){
				var div = this;
				this.timer /* timer라는 속성 추가 */ = setTimeout(function(){
					div.classList.add("image-magnified");
				}, 1000);
			});
			div.addEventListener("mouseout", function(){
				clearTimeout(this.timer); /* mouseover 이벤트 해제를 위해 */
				this.classList.remove("image-magnified");
			});
		}
	}
};
xhttp.open("GET", "image_list.txt");
xhttp.send();
function selectAll(btn) {
	var divs = document.getElementsByTagName("div");
	for(var i=0; i<divs.length; i++) {
		if(btn.value == "Select All") {
			divs[i].classList.add("image-selected");
		}else {
			divs[i].classList.remove("image-selected");
		}
	}
	if(btn.value == "Select All") {
		btn.value = "Unselect All";
	} else {
		btn.value = "Select All";
	}
}
function slideShow(btn) {
	var divs = document.getElementsByTagName("div");
	var index = 0;
	divs[index].classList.add("image-magnified");
	var timer = setInterval(function(){
		divs[index].classList.remove("image-magnified");
		index++;
		if(index<divs.length) {
			divs[index].classList.add("image-magnified");
		} else {
			clearInterval(timer);
		}
	}, 2000);
}
