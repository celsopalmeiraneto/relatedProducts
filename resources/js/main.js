$(document).ready(function(){
  var input = document.getElementById('product');

  fillAutoComplete();

  function fillAutoComplete() {
    $.get("src/api/Product", function (data) {

      var list = data.map(function(it){
        return {label:it.name, value:it.productId};
      });

      var impAutocomplete = new Awesomplete(input, {
        list: list
      });
      console.log(impAutocomplete);
    });

  }
});
