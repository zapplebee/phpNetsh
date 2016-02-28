"use strict";

function phpnetshinit(){

  var post = function(formData,success,error,init){
  init();
  var xhr = new XMLHttpRequest();
  xhr.open("post", 'api.php');
  xhr.send(formData);
  xhr.onreadystatechange = function () {
    if (xhr.readyState === 4) {
      if (xhr.status === 200) {
        success(JSON.parse(xhr.responseText), xhr.status);
      } else {
        error(JSON.parse(xhr.responseText), xhr.status);
      }
    }
  };
      
}

    
}