$(document).ready(function(){
  var input = document.getElementById('product');

  var fillAutoComplete = function(){
    $.get("src/api/Product", function (data) {

      var list = data.map(function(it){
        return {label:(it.name+' - '+it.productId), value:it.productId};
      });

      var impAutocomplete = new Awesomplete(input, {
        list: list
      });

      $(document).on("awesomplete-selectcomplete", function(event){
        changeTexts(event.originalEvent.text);
        executeQueries(event.originalEvent.text);
      });
    });
  };
  var changeTexts = function(evtData){
    $("#productName").text(evtData.label);
    $("#query_neo").text("");
    $("#query_mysql").text("");
    $("#progress_neo").show();
    $("#progress_mysql").show();
  };

  var executeQueries = function(evtData){
    $.get("src/api/SearchRelated/{{id}}/neo".replace("{{id}}"  ,evtData.value), function(data){
      renderNeo4j(data);
      render(data, "neo");
    });
    $.get("src/api/SearchRelated/{{id}}/mysql".replace("{{id}}",evtData.value), function(data){
      render(data, "mysql");
    });
  }

  var renderNeo4j = function(data){
    var tbl = $("#relatedProducts");
    tbl.html("");
    var tableContent = "";
    data.res.forEach(function(el){
      tableContent += "<tr><td>{{name}}</td><td>{{occ}}</td></tr>"
        .replace("{{name}}",el.name)
        .replace("{{occ}}",el.occurrences);
    });
    tbl.html(tableContent);
  };

  var render = function(data, type){
    var query = $("#query_"+type);
    var time  = $("#totalTime_"+type);
    var prog  = $("#progress_"+type);

    if (query && time) {
      query.text(data.query);
      time.text(data.time);
      prog.hide();
    }
  };


  fillAutoComplete();
});
